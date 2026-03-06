<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ManagedProperty;
use App\Models\ManagedPropertyExpense;
use App\Models\ManagedPropertySale;
use App\Models\ManagedPropertyRental;
use App\Services\CashAccountingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManagedPropertyController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────
    public function index()
    {
        $properties = ManagedProperty::withCount('expenses')
            ->with(['sale', 'activeRental'])
            ->latest()
            ->get();

        $stats = [
            'total'      => $properties->count(),
            'flip'       => $properties->where('type', 'flip')->count(),
            'rental'     => $properties->where('type', 'rental')->count(),
            'active'     => $properties->whereIn('status', ['active', 'rented'])->count(),
            'sold'       => $properties->where('status', 'sold')->count(),
        ];

        return view('managed.index', compact('properties', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────
    public function create()
    {
        return view('managed.create');
    }

    // ── Store ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name'               => ['required', 'string', 'max:255'],
            'owner_phone'              => ['required', 'string', 'max:30'],
            'owner_email'              => ['nullable', 'email', 'max:255'],
            'address'                  => ['required', 'string', 'max:255'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'area'                     => ['nullable', 'string', 'max:150'],
            'bedrooms'                 => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'                => ['nullable', 'integer', 'min:0', 'max:20'],
            'area_sqm'                 => ['nullable', 'numeric', 'min:0'],
            'description'              => ['nullable', 'string', 'max:2000'],
            'type'                     => ['required', Rule::in(['flip', 'rental'])],
            'owner_asking_price'       => ['required', 'numeric', 'min:0'],
            'estimated_renovation_cost'=> ['nullable', 'numeric', 'min:0'],
            'agreed_listing_price'     => ['nullable', 'numeric', 'min:0'],
            'agreed_rent_price'        => ['nullable', 'numeric', 'min:0'],
            'company_commission_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'agreement_date'           => ['required', 'date'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status']     = 'active';
        $validated['estimated_renovation_cost'] ??= 0;

        $property = ManagedProperty::create($validated);

        // Generate owner agreement PDF
        $this->generateAgreementPdf($property);

        $this->audit('Create', 'Managed Property', "Created managed property: {$property->address}");

        return redirect()->route('managed.show', $property)
            ->with('success', 'Property added successfully. Owner agreement PDF generated.');
    }

    // ── Show ──────────────────────────────────────────────────────
    public function show(ManagedProperty $property)
    {
        $property->load([
            'expenses',
            'sale',
            'rentals.payments',
        ]);

        $totalExpenses   = $property->totalExpenses();
        $activeRental    = $property->rentals->where('status', 'active')->first();

        // Flip stats
        $flipProfit = null;
        if ($property->isFlip() && $property->sale) {
            $flipProfit = (float)$property->sale->sale_price
                       - (float)$property->sale->owner_payout_amount
                       - $totalExpenses;
        }

        // Rental stats
        $rentalStats = null;
        if ($property->isRental()) {
            $allPayments = $property->rentals->flatMap->payments;
            $rentalStats = [
                'total_collected'  => $allPayments->whereNotNull('collected_at')->sum('amount_collected'),
                'total_owner_paid' => $allPayments->whereNotNull('owner_paid_at')->sum('owner_paid_amount'),
                'total_commission' => $allPayments->where('status', 'owner_paid')->sum('company_commission'),
                'pending_count'    => $allPayments->where('status', 'pending')->count(),
            ];
        }

        return view('managed.show', compact(
            'property', 'totalExpenses', 'activeRental', 'flipProfit', 'rentalStats'
        ));
    }

    // ── Edit ──────────────────────────────────────────────────────
    public function edit(ManagedProperty $property)
    {
        return view('managed.edit', compact('property'));
    }

    // ── Update ────────────────────────────────────────────────────
    public function update(Request $request, ManagedProperty $property)
    {
        $validated = $request->validate([
            'owner_name'               => ['required', 'string', 'max:255'],
            'owner_phone'              => ['required', 'string', 'max:30'],
            'owner_email'              => ['nullable', 'email', 'max:255'],
            'address'                  => ['required', 'string', 'max:255'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'area'                     => ['nullable', 'string', 'max:150'],
            'bedrooms'                 => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'                => ['nullable', 'integer', 'min:0', 'max:20'],
            'area_sqm'                 => ['nullable', 'numeric', 'min:0'],
            'description'              => ['nullable', 'string', 'max:2000'],
            'owner_asking_price'       => ['required', 'numeric', 'min:0'],
            'estimated_renovation_cost'=> ['nullable', 'numeric', 'min:0'],
            'agreed_listing_price'     => ['nullable', 'numeric', 'min:0'],
            'agreed_rent_price'        => ['nullable', 'numeric', 'min:0'],
            'company_commission_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'agreement_date'           => ['required', 'date'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
        ]);

        $property->update($validated);
        $this->regenerateAgreementPdf($property);

        $this->audit('Update', 'Managed Property', "Updated managed property: {$property->address}");

        return redirect()->route('managed.show', $property)
            ->with('success', 'Property updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────
    public function destroy(ManagedProperty $property)
    {
        // Cannot delete if there's a pending owner payout
        if ($property->isFlip() && $property->sale && !$property->sale->owner_paid_at) {
            return back()->with('error', 'Cannot delete: owner payout is still pending.');
        }

        if ($property->pdf_path) {
            Storage::disk('public')->delete($property->pdf_path);
        }

        $this->audit('Delete', 'Managed Property', "Deleted managed property: {$property->address}");
        $property->delete();

        return redirect()->route('managed.index')
            ->with('success', 'Property deleted.');
    }

    // ── Terminate ─────────────────────────────────────────────────
    public function terminate(Request $request, ManagedProperty $property)
    {
        $property->update(['status' => 'terminated']);

        // End any active rental
        $property->activeRental?->update([
            'status'          => 'terminated',
            'actual_end_date' => now()->toDateString(),
        ]);

        $this->audit('Update', 'Managed Property', "Terminated managed property: {$property->address}");

        return redirect()->route('managed.show', $property)
            ->with('success', 'Agreement terminated.');
    }

    // ── Agreement PDF ─────────────────────────────────────────────
    public function agreementPdf(ManagedProperty $property)
    {
        $property->load('expenses');

        $logoPath = public_path('img/abosaleh-logo.png');
        $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $signaturePath = public_path('img/abousaleh-signature.png');
        $signatureB64  = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

        $pdf = Pdf::loadView('pdfs.managed.agreement', compact('property', 'logoB64', 'signatureB64'))
                  ->setPaper('a4');

        return $pdf->stream("agreement-{$property->id}.pdf");
    }

    // ── Private helpers ───────────────────────────────────────────
    private function generateAgreementPdf(ManagedProperty $property): void
    {
        try {
            $logoPath = public_path('img/abosaleh-logo.png');
            $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
            $signaturePath = public_path('img/abousaleh-signature.png');
            $signatureB64  = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

            $pdf  = Pdf::loadView('pdfs.managed.agreement', compact('property', 'logoB64', 'signatureB64'))
                       ->setPaper('a4');
            $path = "managed/agreements/agreement-{$property->id}.pdf";
            Storage::disk('public')->put($path, $pdf->output());
            $property->update(['pdf_path' => $path]);
        } catch (\Throwable $e) {
            // non-fatal
        }
    }

    private function regenerateAgreementPdf(ManagedProperty $property): void
    {
        if ($property->pdf_path) {
            Storage::disk('public')->delete($property->pdf_path);
        }
        $this->generateAgreementPdf($property);
    }

    private function audit(string $event, string $entity, string $details): void
    {
        $audit = new AuditLog();
        $audit->user_id     = auth()->id();
        $audit->event       = $event;
        $audit->entity_type = $entity;
        $audit->details     = $details;
        $audit->save();
        $audit->record = 'MP-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();
    }
}
