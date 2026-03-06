<?php

namespace App\Http\Controllers;

use App\Models\ManagedProperty;
use App\Models\ManagedPropertyExpense;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;

class ManagedPropertyExpenseController extends Controller
{
    public function store(Request $request, ManagedProperty $property, CashAccountingService $cash)
    {
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

        // Post to ledger
        $cash->postManagedPropertyExpense($expense, auth()->id());

        return redirect()->route('managed.show', $property)
            ->with('success', 'Expense recorded: $' . number_format($expense->amount, 2));
    }

    public function destroy(
        ManagedProperty $property,
        ManagedPropertyExpense $expense,
        CashAccountingService $cash
    ) {
        if ($expense->managed_property_id !== $property->id) {
            abort(403);
        }
        if ($expense->isVoided()) {
            return back()->with('error', 'Expense is already voided.');
        }

        $cash->voidManagedPropertyExpense($expense, 'Deleted by user', auth()->id());

        return redirect()->route('managed.show', $property)
            ->with('success', 'Expense voided.');
    }
}