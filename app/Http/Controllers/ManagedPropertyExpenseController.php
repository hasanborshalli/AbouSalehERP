<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ManagedProperty;
use App\Models\ManagedPropertyExpense;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagedPropertyExpenseController extends Controller
{
    public function store(Request $request, ManagedProperty $property, CashAccountingService $cash)
    {
        // Support two modes: manual cash expense OR inventory item draw
        $mode = $request->input('expense_mode', 'cash'); // 'cash' or 'inventory'

        if ($mode === 'inventory') {
            $data = $request->validate([
                'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
                'quantity_used'     => ['required', 'numeric', 'min:0.01'],
                'expense_date'      => ['required', 'date'],
                'description'       => ['nullable', 'string', 'max:255'],
                'notes'             => ['nullable', 'string', 'max:500'],
            ]);

            $item = InventoryItem::findOrFail($data['inventory_item_id']);

            if ((float)$item->quantity < (float)$data['quantity_used']) {
                return back()->with('error', "Insufficient stock. Available: {$item->quantity} {$item->unit}.");
            }

            // Calculate cost using item's current price
            $unitCost    = (float)$item->price;
            $totalCost   = round($unitCost * (float)$data['quantity_used'], 2);
            $description = $data['description'] ?: "Inventory: {$item->name} × {$data['quantity_used']} {$item->unit}";

            DB::transaction(function () use ($data, $property, $item, $totalCost, $description, $cash) {
                // Deduct from stock
                $item->quantity = max(0, (float)$item->quantity - (float)$data['quantity_used']);
                $item->is_out_of_stock = ($item->quantity <= 0);
                $item->save();

                $expense = ManagedPropertyExpense::create([
                    'managed_property_id' => $property->id,
                    'description'         => $description,
                    'category'            => 'materials',
                    'amount'              => $totalCost,
                    'expense_date'        => $data['expense_date'],
                    'vendor_name'         => $item->name . ' (Inventory)',
                    'notes'               => $data['notes'] ?? null,
                    'created_by'          => auth()->id(),
                ]);

                $cash->postManagedPropertyExpense($expense, auth()->id());
            });

            return redirect()->route('managed.show', $property)
                ->with('success', "Used {$data['quantity_used']} {$item->unit} of {$item->name} — cost: $" . number_format($totalCost, 2));
        }

        // ── Cash / manual expense ────────────────────────────────
        $data = $request->validate([
            'description'  => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:80'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'vendor_name'  => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $data['managed_property_id'] = $property->id;
        $data['created_by']          = auth()->id();

        $expense = ManagedPropertyExpense::create($data);
        $cash->postManagedPropertyExpense($expense, auth()->id());

        return redirect()->route('managed.show', $property)
            ->with('success', 'Expense recorded: $' . number_format($expense->amount, 2));
    }

    public function destroy(
        ManagedProperty $property,
        ManagedPropertyExpense $expense,
        CashAccountingService $cash
    ) {
        if ($expense->managed_property_id !== $property->id) abort(403);
        if ($expense->isVoided()) return back()->with('error', 'Expense is already voided.');

        $cash->voidManagedPropertyExpense($expense, 'Deleted by user', auth()->id());

        return redirect()->route('managed.show', $property)->with('success', 'Expense voided.');
    }
}