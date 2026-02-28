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
        $audit = new AuditLog();
        $audit->user_id = auth()->id();
        $audit->event = 'Create';
        $audit->entity_type = 'Inventory Purchase';
        $audit->details = 'Creating inventory purchase receipt failed';
        $audit->save();
        $audit->record = 'ACC-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        // Receipt-level fields (shared across all line items)
        $header = $request->validate([
            'purchase_date' => ['required', 'date'],
            'vendor_name'   => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        // Line items validation
        $request->validate([
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'items.*.qty'               => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'         => ['required', 'numeric', 'min:0'],
        ]);

        $lines = $request->input('items');
        $savedNames = [];

        // One receipt reference shared by all lines in this submission
        $receiptRef = 'RCPT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        foreach ($lines as $line) {
            $cash->createInventoryPurchase([
                'receipt_ref'       =>          $receiptRef,
                'inventory_item_id' => (int)   $line['inventory_item_id'],
                'purchase_date'     =>          $header['purchase_date'],
                'qty'               => (int)    $line['qty'],
                'unit_cost'         => (float)  $line['unit_cost'],
                'vendor_name'       =>          $header['vendor_name'] ?? null,
                'notes'             =>          $header['notes'] ?? null,
            ], auth()->id());

            $item = InventoryItem::find($line['inventory_item_id']);
            if ($item) $savedNames[] = $item->name;
        }

        $audit->details = 'Creating inventory purchase receipt succeeded (' . count($lines) . ' item(s): ' . implode(', ', $savedNames) . ')';
        $audit->save();

        $label = count($savedNames) === 1 ? $savedNames[0] : count($savedNames) . ' items';

        return redirect()
            ->route('accounting.purchases')
            ->with('success', 'Receipt saved. Stock updated for: ' . $label);
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