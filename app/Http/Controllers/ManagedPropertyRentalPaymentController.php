<?php

namespace App\Http\Controllers;

use App\Models\ManagedProperty;
use App\Models\ManagedPropertyRental;
use App\Models\ManagedPropertyRentalPayment;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagedPropertyRentalPaymentController extends Controller
{
    public function markCollected(
        Request $request,
        ManagedProperty $property,
        ManagedPropertyRental $rental,
        ManagedPropertyRentalPayment $payment,
        CashAccountingService $cash
    ) {
        if ($rental->managed_property_id !== $property->id) abort(403);
        if ($payment->managed_property_rental_id !== $rental->id) abort(403);
        if ($payment->status !== 'pending') return back()->with('error', 'Payment already collected.');

        $data = $request->validate([
            'amount_collected' => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $amountCollected = (float)($data['amount_collected'] ?? $payment->amount_due);

        DB::transaction(function () use ($payment, $amountCollected, $data, $cash) {
            $payment->update([
                'amount_collected' => $amountCollected,
                'collected_at'     => now(),
                'status'           => 'collected',
                'notes'            => $data['notes'] ?? $payment->notes,
            ]);
            $cash->postRentalPaymentCollected($payment, auth()->id());
        });

        return redirect()->route('managed.show', $property)
            ->with('success', 'Rent collected: $' . number_format($amountCollected, 2));
    }

    public function markOwnerPaid(
        Request $request,
        ManagedProperty $property,
        ManagedPropertyRental $rental,
        ManagedPropertyRentalPayment $payment,
        CashAccountingService $cash
    ) {
        if ($rental->managed_property_id !== $property->id) abort(403);
        if ($payment->managed_property_rental_id !== $rental->id) abort(403);
        if ($payment->status !== 'collected') return back()->with('error', 'Rent must be collected first.');

        DB::transaction(function () use ($payment, $cash) {
            $payment->update([
                'owner_paid_amount' => $payment->owner_share,
                'owner_paid_at'     => now(),
                'status'            => 'owner_paid',
            ]);
            $cash->postRentalOwnerPayout($payment, auth()->id());
        });

        return redirect()->route('managed.show', $property)
            ->with('success', 'Owner paid: $' . number_format($payment->owner_share, 2));
    }
}