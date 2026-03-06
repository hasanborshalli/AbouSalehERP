<?php

namespace App\Http\Controllers;

use App\Models\ManagedProperty;
use App\Models\ManagedPropertyRental;
use App\Models\ManagedPropertyRentalPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ManagedPropertyRentalController extends Controller
{
    public function store(Request $request, ManagedProperty $property)
    {
        if ($property->type !== 'rental') return back()->with('error', 'This property is not a rental type.');
        if ($property->rentals()->where('status', 'active')->exists()) {
            return back()->with('error', 'There is already an active rental. End it first.');
        }

        $data = $request->validate([
            'tenant_name'            => ['required', 'string', 'max:255'],
            'tenant_phone'           => ['nullable', 'string', 'max:30'],
            'tenant_email'           => ['nullable', 'email', 'max:255'],
            'monthly_rent'           => ['required', 'numeric', 'min:0'],
            'company_commission_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'deposit_amount'         => ['nullable', 'numeric', 'min:0'],
            'start_date'             => ['required', 'date'],
            'end_date'               => ['required', 'date', 'after:start_date'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $monthlyRent = (float)$data['monthly_rent'];
        $commPct     = (float)$data['company_commission_pct'];
        $commission  = round($monthlyRent * $commPct / 100, 2);
        $ownerShare  = round($monthlyRent - $commission, 2);

        $data['managed_property_id']       = $property->id;
        $data['owner_monthly_share']        = $ownerShare;
        $data['company_monthly_commission'] = $commission;
        $data['deposit_amount']            ??= 0;
        $data['created_by']                 = auth()->id();

        $rental = null;
        DB::transaction(function () use ($data, $property, $monthlyRent, $ownerShare, $commission, &$rental) {
            $rental = ManagedPropertyRental::create($data);

            // Generate monthly payment schedule
            $month = Carbon::parse($rental->start_date)->startOfDay();
            $end   = Carbon::parse($rental->end_date)->startOfDay();
            while ($month->lte($end)) {
                ManagedPropertyRentalPayment::create([
                    'managed_property_rental_id' => $rental->id,
                    'due_date'           => $month->toDateString(),
                    'amount_due'         => $monthlyRent,
                    'owner_share'        => $ownerShare,
                    'company_commission' => $commission,
                    'status'             => 'pending',
                    'created_by'         => auth()->id(),
                ]);
                $month->addMonth();
            }

            $property->update(['status' => 'rented']);
            $this->generateRentalPdf($rental);
        });

        // Send contract email to tenant
        if ($rental && $rental->tenant_email) {
            try {
                Mailable::to($rental->tenant_email)
                    ->queue(new \App\Mail\ManagedPropertyRentalMail($property, $rental));
            } catch (\Throwable $e) {
                Log::error('Rental email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('managed.show', $property)
            ->with('success', 'Rental contract created.' . ($rental->tenant_email ? ' Contract emailed to tenant.' : ''));
    }

    public function end(Request $request, ManagedProperty $property, ManagedPropertyRental $rental)
    {
        if ($rental->managed_property_id !== $property->id) abort(403);

        $rental->update(['status' => 'ended', 'actual_end_date' => now()->toDateString()]);
        $rental->payments()->where('status','pending')->where('due_date','>',now()->toDateString())->delete();

        if (!$property->rentals()->where('status','active')->exists()) {
            $property->update(['status' => 'active']);
        }

        return redirect()->route('managed.show', $property)->with('success', 'Rental ended.');
    }

    public function contractPdf(ManagedProperty $property, ManagedPropertyRental $rental)
    {
        if ($rental->managed_property_id !== $property->id) abort(403);
        $rental->load('payments');

        $logoPath = public_path('img/abosaleh-logo.png');
        $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $signaturePath = public_path('img/abousaleh-signature.png');
        $signatureB64  = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

        $pdf = Pdf::loadView('pdfs.managed.rental-contract', compact('property','rental','logoB64','signatureB64'))
                  ->setPaper('a4');
        return $pdf->stream("rental-contract-{$rental->id}.pdf");
    }

    private function generateRentalPdf(ManagedPropertyRental $rental): void
    {
        try {
            $property = $rental->property;
            $rental->load('payments');
            $logoPath = public_path('img/abosaleh-logo.png');
            $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
            $signaturePath = public_path('img/abousaleh-signature.png');
            $signatureB64  = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

            $pdf  = Pdf::loadView('pdfs.managed.rental-contract', compact('property','rental','logoB64','signatureB64'))
                       ->setPaper('a4');
            $path = "managed/rentals/rental-{$rental->id}.pdf";
            Storage::disk('public')->put($path, $pdf->output());
            $rental->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {}
    }
}