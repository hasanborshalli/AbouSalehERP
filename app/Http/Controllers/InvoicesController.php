<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Jobs\GenerateInKindReceiptJob;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\InKindPayment;
use App\Models\InKindPaymentItem;
use App\Services\CashAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\GenerateReceiptPdfJob;
use App\Jobs\SendInvoiceReceiptMailJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    public function updateDates(Request $request, Invoice $invoice)
    {
        $audit = new AuditLog();
        $audit->user_id = auth()->id();
        $audit->event = 'Update';
        $audit->entity_type = 'Invoice';
        $audit->details = 'Updating invoice date (' . $invoice->invoice_number . ') failed';
        $audit->save();
        $audit->record = 'INV-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        $data = $request->validate([
            'issue_date' => ['nullable', 'date'],
            'due_date'   => ['nullable', 'date', 'after_or_equal:issue_date'],
        ]);

        $invoice->update([
            'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
            'due_date'   => $data['due_date']   ?? $invoice->due_date,
        ]);

        GenerateInvoicePdfJob::dispatchSync($invoice->id);
        $audit->details = 'Updating invoice date (' . $invoice->invoice_number . ') succeeded';
        $audit->save();
        return response()->json(['message' => 'Dates updated + PDF regenerated']);
    }

    public function markPaid(Request $request, Invoice $invoice, CashAccountingService $acct)
    {
        $audit = new AuditLog();
        $audit->user_id = auth()->id();
        $audit->event = 'Update';
        $audit->entity_type = 'Invoice';
        $audit->details = 'Marking invoice (' . $invoice->invoice_number . ') as paid failed';
        $audit->save();
        $audit->record = 'INV-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-' . $audit->id;
        $audit->save();

        $paymentType = $request->input('payment_type', 'cash');

        // ── IN-KIND PAYMENT ───────────────────────────────────────────────
        if ($paymentType === 'in_kind') {
            // Strip out any empty/incomplete rows before validation
            $rawItems = collect($request->input('items', []))
                ->filter(fn($row) => !empty($row['inventory_item_id']) && !empty($row['quantity']))
                ->values()
                ->toArray();
            $request->merge(['items' => $rawItems]);

            $data = $request->validate([
                'items'                     => ['required', 'array', 'min:1'],
                'items.*.inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
                'items.*.quantity'          => ['required', 'numeric', 'min:0.001'],
                'items.*.notes'             => ['nullable', 'string', 'max:255'],
                'payment_notes'             => ['nullable', 'string', 'max:1000'],
            ]);

            $inKindPayment = null;

            DB::transaction(function () use ($request, $data, $invoice, $acct, &$inKindPayment) {
                // 1. Mark invoice paid
                $invoice->status  = 'paid';
                $invoice->paid_at = now();
                $invoice->save();

                // 2. Create InKindPayment record
                $inKindPayment = InKindPayment::create([
                    'contract_id'           => $invoice->contract_id,
                    'invoice_id'            => $invoice->id,
                    'payment_date'          => now()->toDateString(),
                    'notes'                 => $data['payment_notes'] ?? null,
                    'created_by'            => auth()->id(),
                    'total_estimated_value' => 0, // will update below
                ]);

                $totalValue = 0;

                // 3. Save items
                foreach ($data['items'] as $row) {
                    $item      = InventoryItem::findOrFail($row['inventory_item_id']);
                    $qty       = (float)$row['quantity'];
                    $unitPrice = (float)$item->price;
                    $rowVal    = round($unitPrice * $qty, 2);
                    $totalValue += $rowVal;

                    InKindPaymentItem::create([
                        'in_kind_payment_id' => $inKindPayment->id,
                        'inventory_item_id'  => $item->id,
                        'quantity'           => $qty,
                        'unit_price_snapshot' => $unitPrice,
                        'total_value'        => $rowVal,
                        'notes'              => $row['notes'] ?? null,
                    ]);
                }

                $inKindPayment->update(['total_estimated_value' => $totalValue]);
                $inKindPayment->load('items');

                // 4. Post ledger (INCREASES stock)
                $acct->postInvoicePaidInKind($inKindPayment, auth()->id());

                // 5. Mark apartment sold on first paid invoice
                $invoice->loadMissing('contract.apartment');
                $contract  = $invoice->contract;
                $apartment = $contract?->apartment;

                if ($apartment && $apartment->status !== 'sold') {
                    $paidCount = $contract->invoices()->where('status', 'paid')->count();
                    if ($paidCount === 1) {
                        $apartment->update(['status' => 'sold']);
                    }
                }
            });

            // 6. Generate in-kind receipt PDF
            if ($inKindPayment) {
                GenerateInKindReceiptJob::dispatch($inKindPayment->id, auth()->id());
            }

            GenerateInvoicePdfJob::dispatch($invoice->id);

            $audit->details = 'Marking invoice (' . $invoice->invoice_number . ') as paid (in-kind) succeeded';
            $audit->save();

            return response()->json(['message' => 'Marked as paid (in-kind). Items added to stock.']);
        }

        // ── CASH PAYMENT ─────────────────────────────────────────────────
        $data = $request->validate([
            'paid_at' => ['nullable', 'date'],
        ]);

        $invoice->status  = 'paid';
        $paidAt = $invoice->paid_at ? Carbon::parse($invoice->paid_at) : now();
        $invoice->paid_at = $paidAt;
        $invoice->save();

        GenerateInvoicePdfJob::dispatch($invoice->id);
        $acct->postInvoicePaid($invoice, $paidAt, auth()->id());

        $invoice->loadMissing('contract.apartment');
        $contract  = $invoice->contract;
        $apartment = $contract?->apartment;

        if ($apartment && $apartment->status !== 'sold') {
            $paidCount = $contract->invoices()->where('status', 'paid')->count();
            if ($paidCount === 1) {
                $apartment->update(['status' => 'sold']);
            }
        }

        DB::afterCommit(function () use ($invoice) {
            Bus::chain([
                new GenerateReceiptPdfJob($invoice->id, auth()->id()),
                new SendInvoiceReceiptMailJob($invoice->id),
            ])->dispatch();
        });

        $audit->details = 'Marking invoice (' . $invoice->invoice_number . ') as paid succeeded';
        $audit->save();
        return response()->json(['message' => 'Marked as paid']);
    }

    /**
     * Return active inventory items as JSON (used by in-kind pickers).
     */
    public function inventoryItemsJson()
    {
        $items = InventoryItem::whereNull('deleted_at')
            ->select('id', 'name', 'price', 'quantity', 'unit')
            ->orderBy('name')
            ->get();

        return response()->json($items);
    }
}