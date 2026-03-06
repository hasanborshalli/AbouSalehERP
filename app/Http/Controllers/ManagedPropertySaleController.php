<?php

namespace App\Http\Controllers;

use App\Models\ManagedProperty;
use App\Models\ManagedPropertySale;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ManagedPropertySaleController extends Controller
{
    public function store(Request $request, ManagedProperty $property, CashAccountingService $cash)
    {
        if ($property->type !== 'flip') return back()->with('error', 'This property is not a flip type.');
        if ($property->sale) return back()->with('error', 'A sale has already been recorded for this property.');

        $data = $request->validate([
            'buyer_name'  => ['required', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'buyer_email' => ['nullable', 'email', 'max:255'],
            'sale_price'  => ['required', 'numeric', 'min:0'],
            'sale_date'   => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $data['managed_property_id'] = $property->id;
        $data['owner_payout_amount'] = $property->owner_asking_price;
        $data['created_by']          = auth()->id();

        $sale = null;
        DB::transaction(function () use ($data, $property, $cash, &$sale) {
            $sale = ManagedPropertySale::create($data);
            $property->update(['status' => 'sold']);
            $cash->postManagedPropertySaleIncome($sale, auth()->id());
        });

        // Send email to buyer
        if ($sale && $sale->buyer_email) {
            try {
                Mail::to($sale->buyer_email)
                    ->queue(new \App\Mail\ManagedPropertySaleMail($property, $sale));
            } catch (\Throwable $e) {
                Log::error('Sale email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('managed.show', $property)
            ->with('success', 'Sale recorded.' . ($sale->buyer_email ? ' Confirmation email sent to buyer.' : '') . ' Remember to pay the owner when ready.');
    }

    public function markOwnerPaid(Request $request, ManagedProperty $property, CashAccountingService $cash)
    {
        $sale = $property->sale;
        if (!$sale) return back()->with('error', 'No sale recorded.');
        if ($sale->owner_paid_at) return back()->with('error', 'Owner has already been paid.');

        DB::transaction(function () use ($sale, $cash) {
            $sale->update(['owner_paid_at' => now()]);
            $cash->postManagedPropertyOwnerPayout($sale, auth()->id());
        });

        return redirect()->route('managed.show', $property)
            ->with('success', 'Owner payout of $' . number_format($sale->owner_payout_amount, 2) . ' recorded.');
    }
}