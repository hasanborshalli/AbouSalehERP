<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\OperatingExpense;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function storePurchase(Request $request, CashAccountingService $cash)
    {
        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'purchase_date'     => ['required', 'date'],
            'qty'               => ['required', 'integer', 'min:1'],
            'unit_cost'         => ['required', 'numeric', 'min:0'],
            'vendor_name'       => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:255'],
        ]);

        // Optional extra safety: ensure qty remains integer-safe for unsignedInteger stock
        $data['qty'] = (int) $data['qty'];

        $cash->createInventoryPurchase([
            'inventory_item_id' => $data['inventory_item_id'],
            'purchase_date'     => $data['purchase_date'],
            'qty'               => $data['qty'],
            'unit_cost'         => (float) $data['unit_cost'],
            'vendor_name'       => $data['vendor_name'] ?? null,
            'notes'             => $data['notes'] ?? null,
        ], auth()->id());

        $item = InventoryItem::find($data['inventory_item_id']);

        return redirect()
            ->route('accounting.purchases')
            ->with('success', 'Purchase saved. Stock updated for: ' . ($item?->name ?? 'Item'));
    }

    public function storeExpense(Request $request, CashAccountingService $cash)
    {
        $data = $request->validate([
            'expense_date' => ['required', 'date'],
            'category'     => ['required', 'string', 'max:80'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        $cash->createOperatingExpense([
            'expense_date' => $data['expense_date'],
            'category'     => $data['category'],
            'amount'       => (float) $data['amount'],
            'description'  => $data['description'] ?? null,
        ], auth()->id());

        return redirect()
            ->route('accounting.expenses')
            ->with('success', 'Expense saved.');
    }
    public function voidPurchase(Request $request, InventoryPurchase $purchase, CashAccountingService $cash)
{
    $data = $request->validate([
        'reason' => ['required','string','max:255'],
    ]);

    $cash->voidInventoryPurchase($purchase, $data['reason'], auth()->id());

    return redirect()->route('accounting.overview')->with('success', 'Purchase voided.');
}

public function voidExpense(Request $request, OperatingExpense $expense, CashAccountingService $cash)
{
    $data = $request->validate([
        'reason' => ['required','string','max:255'],
    ]);

    $cash->voidOperatingExpense($expense, $data['reason'], auth()->id());

    return redirect()->route('accounting.overview')->with('success', 'Expense voided.');
}
}