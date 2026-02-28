<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\InventoryPurchase;
use App\Models\LedgerEntry;
use App\Models\OperatingExpense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashAccountingService
{
    // System accounts
    private function cashAccount(): Account
    {
        return Account::where('code', '1000')->firstOrFail();
    }

    private function revenueAccount(): Account
    {
        return Account::where('code', '4000')->firstOrFail();
    }

    private function purchasesAccount(): Account
    {
        return Account::where('code', '5100')->firstOrFail();
    }

    private function operatingExpenseAccount(): Account
    {
        return Account::where('code', '6000')->firstOrFail();
    }

    /**
     * CASH BASIS:
     * When an invoice is marked PAID, we post cash-in revenue.
     */
    public function postInvoicePaid(Invoice $invoice, ?Carbon $paidAt = null, ?int $userId = null): void
    {
        $paidAt = $paidAt ?? now();

        // avoid double-posting
        $exists = LedgerEntry::where('source_type', 'invoice')
            ->where('source_id', $invoice->invoice_number)
            ->exists();

        if ($exists) return;
        $totalPaid=(float)$invoice->amount + (float)$invoice->late_fee_amount;
        DB::transaction(function () use ($invoice, $paidAt, $userId,$totalPaid) {
            LedgerEntry::create([
                'posted_at' => $paidAt,
                'account_id' => $this->revenueAccount()->id,
                'amount' => $totalPaid,
                'direction' => 'in',
                'description' => 'Invoice paid: #' . $invoice->invoice_number,
                'source_type' => 'invoice',
                'source_id' => $invoice->invoice_number,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * CASH BASIS:
     * Record an inventory purchase (restock) as cash-out expense.
     * Also increases InventoryItem quantity.
     */
  public function createInventoryPurchase(array $data, ?int $userId = null): InventoryPurchase
{
    $purchaseDate = \Carbon\Carbon::parse($data['purchase_date'])->startOfDay();

    // your inventory_items.quantity is unsignedInteger => keep qty integer
    $qty = (int) $data['qty'];

    $unitCost = (float) $data['unit_cost'];
    $total = round($qty * $unitCost, 2);

    return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $purchaseDate, $qty, $unitCost, $total, $userId) {
        /** @var \App\Models\InventoryItem $item */
        $item = \App\Models\InventoryItem::lockForUpdate()->findOrFail($data['inventory_item_id']);

        $purchase = InventoryPurchase::create([
    'receipt_ref'       => $data['receipt_ref'] ?? null,
    'inventory_item_id' => $item->id,
    'purchase_date' => $purchaseDate->toDateString(),
    'qty' => $qty,
    'unit_cost' => $unitCost,
    'total_cost' => $total,
    'vendor_name' => $data['vendor_name'] ?? null,
    'payment_method' => $data['payment_method'] ?? 'cash',
    'notes' => $data['notes'] ?? null,
    'created_by' => $userId,
]);

        // Update stock (integer-safe)
        $item->quantity = (int)$item->quantity + $qty;

        // Auto out-of-stock flag
        $item->is_out_of_stock = ((int)$item->quantity <= 0);

        $item->save();

        // Ledger cash-out expense
        \App\Models\LedgerEntry::create([
            'posted_at' => $purchaseDate,
            'account_id' => $this->purchasesAccount()->id,
            'amount' => $total,
            'direction' => 'out',
            'description' => 'Inventory purchase: ' . ($item->name ?? ('Item#'.$item->id)),
            'source_type' => 'inventory_purchase',
            'source_id' => $purchase->id,
            'user_id' => $userId,
        ]);

        return $purchase;
    });
}

    /**
     * CASH BASIS:
     * Record an operating expense as cash-out.
     */
    public function createOperatingExpense(array $data, ?int $userId = null): OperatingExpense
    {
        $expenseDate = Carbon::parse($data['expense_date'])->startOfDay();
        $amount = round((float)$data['amount'], 2);

        return DB::transaction(function () use ($data, $expenseDate, $amount, $userId) {
            $exp = OperatingExpense::create([
                'expense_date' => $expenseDate->toDateString(),
                'category' => $data['category'],
                'amount' => $amount,
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            LedgerEntry::create([
                'posted_at' => $expenseDate,
                'account_id' => $this->operatingExpenseAccount()->id,
                'amount' => $amount,
                'direction' => 'out',
                'description' => 'Operating expense: ' . $exp->category,
                'source_type' => 'operating_expense',
                'source_id' => $exp->id,
                'user_id' => $userId,
            ]);

            return $exp;
        });
    }

    /**
     * Report: totals per month (cash-basis)
     */
    public function lastMonthsSummary(int $months = 6): array
    {
        $range = collect(range($months - 1, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'key' => $d->format('Y-m'),
                'label' => $d->format('M'),
                'start' => $d->copy()->startOfMonth(),
                'end' => $d->copy()->endOfMonth(),
            ];
        });

        $labels = $range->pluck('label')->values();

        $revenues = [];
        $expenses = [];
        $net = [];

        foreach ($range as $m) {
            $rev = LedgerEntry::whereBetween('posted_at', [$m['start'], $m['end']])
                ->where('direction', 'in')
                ->sum('amount');

            $exp = LedgerEntry::whereBetween('posted_at', [$m['start'], $m['end']])
                ->where('direction', 'out')
                ->sum('amount');

            $revenues[] = (float)$rev;
            $expenses[] = (float)$exp;
            $net[] = (float)($rev - $exp);
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'expenses' => $expenses,
            'net' => $net,
        ];
    }
    private function postDoubleEntry(
    Carbon $postedAt,
    int $creditAccountId,
    int $debitAccountId,
    float $amount,
    string $description,
    string $sourceType,
    int $sourceId,
    ?int $userId = null
): void {
    // CREDIT entry (money out of cash OR revenue)
    LedgerEntry::create([
        'posted_at'   => $postedAt,
        'account_id'  => $creditAccountId,
        'amount'      => $amount,
        'direction'   => 'out',
        'description' => $description,
        'source_type' => $sourceType,
        'source_id'   => $sourceId,
        'user_id'     => $userId,
    ]);

    // DEBIT entry (cash in OR expense)
    LedgerEntry::create([
        'posted_at'   => $postedAt,
        'account_id'  => $debitAccountId,
        'amount'      => $amount,
        'direction'   => 'in',
        'description' => $description,
        'source_type' => $sourceType,
        'source_id'   => $sourceId,
        'user_id'     => $userId,
    ]);
}
public function voidInventoryPurchase(InventoryPurchase $purchase, string $reason, ?int $userId = null): void
{
    if ($purchase->voided_at) return;

    DB::transaction(function () use ($purchase, $reason, $userId) {

        // Lock purchase + item for safe stock update
        $purchase = InventoryPurchase::lockForUpdate()->findOrFail($purchase->id);
        $item = InventoryItem::lockForUpdate()->findOrFail($purchase->inventory_item_id);

        // Safety: prevent negative stock
        if ((int)$item->quantity < (int)$purchase->qty) {
            throw ValidationException::withMessages([
                'void' => "Cannot void: current stock ({$item->quantity}) is less than purchase qty ({$purchase->qty}).",
            ]);
        }

        // Mark voided
        $purchase->voided_at = now();
        $purchase->voided_by = $userId;
        $purchase->void_reason = $reason;
        $purchase->save();

        // Reverse stock
        $item->quantity = (int)$item->quantity - (int)$purchase->qty;
        $item->is_out_of_stock = ((int)$item->quantity <= 0);
        $item->save();

        // Find original ledger (cash out)
        $original = LedgerEntry::where('source_type', 'inventory_purchase')
            ->where('source_id', $purchase->id)
            ->where('direction', 'out')
            ->first();

        // Create reversal ledger (cash in)
        LedgerEntry::create([
            'posted_at' => now(),
            'account_id' => $this->purchasesAccount()->id,
            'amount' => (float)$purchase->total_cost,
            'direction' => 'in',
            'description' => "VOID purchase #{$purchase->id}: {$reason}",
            'source_type' => 'inventory_purchase_void',
            'source_id' => $purchase->id,
            'user_id' => $userId,
            'reverses_entry_id' => $original?->id,
        ]);
    });
}

public function voidOperatingExpense(OperatingExpense $expense, string $reason, ?int $userId = null): void
{
    if ($expense->voided_at) return;

    DB::transaction(function () use ($expense, $reason, $userId) {

        $expense = OperatingExpense::lockForUpdate()->findOrFail($expense->id);

        $expense->voided_at = now();
        $expense->voided_by = $userId;
        $expense->void_reason = $reason;
        $expense->save();

        $original = LedgerEntry::where('source_type', 'operating_expense')
            ->where('source_id', $expense->id)
            ->where('direction', 'out')
            ->first();

        LedgerEntry::create([
            'posted_at' => now(),
            'account_id' => $this->operatingExpenseAccount()->id,
            'amount' => (float)$expense->amount,
            'direction' => 'in',
            'description' => "VOID expense #{$expense->id}: {$reason}",
            'source_type' => 'operating_expense_void',
            'source_id' => $expense->id,
            'user_id' => $userId,
            'reverses_entry_id' => $original?->id,
        ]);
    });
}
}