<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\OperatingExpense;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function storePurchase(Request $request, CashAccountingService $cash)
    {
         $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Create';
        $audit->entity_type='Inventory Purchase';
        $audit->details='Creating inventory purchase failed';
        $audit->save();
        $audit->record='ACC-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
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
        $audit->details='Creating inventory purchase succeeded';
        $audit->save();
        return redirect()
            ->route('accounting.purchases')
            ->with('success', 'Purchase saved. Stock updated for: ' . ($item?->name ?? 'Item'));
    }

    public function storeExpense(Request $request, CashAccountingService $cash)
    {
         $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Create';
        $audit->entity_type='Operating Expense';
        $audit->details='Creating operating expense failed';
        $audit->save();
        $audit->record='ACC-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
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
        $audit->details='Creating operating expense succeeded';
        $audit->save();
        return redirect()
            ->route('accounting.expenses')
            ->with('success', 'Expense saved.');
    }
    public function voidPurchase(Request $request, InventoryPurchase $purchase, CashAccountingService $cash)
{
     $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Delete';
        $audit->entity_type='Inventory Purchase';
        $audit->details='Voiding inventory purchase ('.$purchase->id.') failed';
        $audit->save();
        $audit->record='ACC-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
    $data = $request->validate([
        'reason' => ['required','string','max:255'],
    ]);

    $cash->voidInventoryPurchase($purchase, $data['reason'], auth()->id());
        $audit->details='Voiding inventory purchase ('.$purchase->id.') succeeded';
            $audit->save();
    return redirect()->route('accounting.overview')->with('success', 'Purchase voided.');
}

public function voidExpense(Request $request, OperatingExpense $expense, CashAccountingService $cash)
{
        $audit=new AuditLog();
            $audit->user_id=auth()->id();
            $audit->event='Delete';
            $audit->entity_type='Operating Expense';
            $audit->details='Voiding operating expense ('.$expense->id.') failed';
            $audit->save();
            $audit->record='ACC-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
            $audit->save();
    $data = $request->validate([
        'reason' => ['required','string','max:255'],
    ]);

    $cash->voidOperatingExpense($expense, $data['reason'], auth()->id());
        $audit->details='Voiding operating expense ('.$expense->id.') succeeded';
            $audit->save();
    return redirect()->route('accounting.overview')->with('success', 'Expense voided.');
}
}