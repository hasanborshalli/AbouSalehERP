<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Jobs\GenerateInKindReceiptJob;
use App\Jobs\GenerateReceiptPdfJob;
use App\Jobs\SendInvoiceReceiptMailJob;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\InKindPayment;
use App\Models\InKindPaymentItem;
use App\Services\CashAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                $invoice->status  = 'paid';
                $invoice->paid_at = now();
                $invoice->save();

                $inKindPayment = InKindPayment::create([
                    'contract_id'           => $invoice->contract_id,
                    'invoice_id'            => $invoice->id,
                    'payment_date'          => now()->toDateString(),
                    'notes'                 => $data['payment_notes'] ?? null,
                    'created_by'            => auth()->id(),
                    'total_estimated_value' => 0,
                ]);

                $totalValue = 0;
                foreach ($data['items'] as $row) {
                    $item      = InventoryItem::findOrFail($row['inventory_item_id']);
                    $qty       = (float)$row['quantity'];
                    $unitPrice = (float)$item->price;
                    $rowVal    = round($unitPrice * $qty, 2);
                    $totalValue += $rowVal;

                    InKindPaymentItem::create([
                        'in_kind_payment_id'  => $inKindPayment->id,
                        'inventory_item_id'   => $item->id,
                        'quantity'            => $qty,
                        'unit_price_snapshot' => $unitPrice,
                        'total_value'         => $rowVal,
                        'notes'               => $row['notes'] ?? null,
                    ]);
                }

                $inKindPayment->update(['total_estimated_value' => $totalValue]);
                $inKindPayment->load('items');
                $acct->postInvoicePaidInKind($inKindPayment, auth()->id());

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

            if ($inKindPayment) {
                GenerateInKindReceiptJob::dispatch($inKindPayment->id, auth()->id());
            }
            GenerateInvoicePdfJob::dispatch($invoice->id);

            $audit->details = 'Marking invoice (' . $invoice->invoice_number . ') as paid (in-kind) succeeded';
            $audit->save();
            return response()->json(['message' => 'Marked as paid (in-kind). Items added to stock.']);
        }

        // ── CASH PAYMENT ──────────────────────────────────────────────────
        $data = $request->validate([
            'paid_at'     => ['nullable', 'date'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDue   = round((float) $invoice->amount + (float) $invoice->late_fee_amount, 2);
        $amountPaid = ($data['amount_paid'] !== null && $data['amount_paid'] !== '')
            ? round((float) $data['amount_paid'], 2)
            : $totalDue;

        // ── Step 1: All DB work in one transaction (fast) ─────────────────
        // We do everything DB-related here — marking paid, ledger entries,
        // apartment status — BEFORE touching any PDF generation.
        // Collecting IDs of invoices whose PDFs need regenerating.
        $adjustedInvoices   = [];
        $pdfInvoiceIds      = []; // will be regenerated after DB work
        $autoPaidInvoiceIds = []; // auto-paid via credit — need receipt PDFs too
        $userId            = auth()->id();

        DB::transaction(function () use (
            $invoice, $data, $acct, $totalDue, $amountPaid, $userId,
            &$adjustedInvoices, &$pdfInvoiceIds, &$autoPaidInvoiceIds
        ) {
            $paidAt = (isset($data['paid_at']) && $data['paid_at'])
                ? Carbon::parse($data['paid_at'])
                : now();

            // Mark current invoice paid
            $invoice->status      = 'paid';
            $invoice->amount_paid = $amountPaid;
            $invoice->paid_at     = $paidAt;
            $invoice->save();

            $pdfInvoiceIds[] = $invoice->id;

            // Ledger entry for current invoice.
            // Pass amountPaid when it exceeds the face value so the ledger reflects actual cash received.
            $ledgerAmount = ($amountPaid > $totalDue + 0.009) ? $amountPaid : null;
            $acct->postInvoicePaid($invoice, $paidAt, $userId, $ledgerAmount);

            // Apartment sold on first payment
            $invoice->loadMissing('contract.apartment');
            $contract  = $invoice->contract;
            $apartment = $contract?->apartment;
            if ($apartment && $apartment->status !== 'sold') {
                $paidCount = $contract->invoices()->where('status', 'paid')->count();
                if ($paidCount === 1) {
                    $apartment->update(['status' => 'sold']);
                }
            }

            // ── Apply credit / deficit ────────────────────────────────────
            $credit = round($amountPaid - $totalDue, 2);

            if ($credit >= 0.01) {
                // OVERPAYMENT: cascade through upcoming invoices
                $upcomingInvoices = Invoice::where('contract_id', $invoice->contract_id)
                    ->where('status', 'pending')
                    ->where('due_date', '>', $invoice->due_date)
                    ->orderBy('due_date')
                    ->get();

                foreach ($upcomingInvoices as $upcoming) {
                    if ($credit < 0.01) break;

                    $upcomingAmount = round((float) $upcoming->amount, 2);

                    if ($credit >= $upcomingAmount) {
                        // Fully covered → auto-pay
                        $credit -= $upcomingAmount;
                        $credit  = round($credit, 2);

                        $upcoming->status      = 'paid';
                        $upcoming->amount_paid = $upcomingAmount;
                        $upcoming->paid_at     = $paidAt;
                        $upcoming->save();

                        // Ledger entry for auto-paid invoice
                        $acct->postInvoicePaid($upcoming, $paidAt, $userId);

                        $pdfInvoiceIds[]      = $upcoming->id; // queue PDF, don't block
                        $autoPaidInvoiceIds[] = $upcoming->id; // also needs a receipt

                        $adjustedInvoices[] = [
                            'id'     => $upcoming->id,
                            'status' => 'paid',
                            'amount' => $upcomingAmount,
                        ];

                    } else {
                        // Partial credit → reduce amount only
                        $newAmount      = round($upcomingAmount - $credit, 2);
                        $upcoming->amount = $newAmount;
                        $upcoming->save();

                        $pdfInvoiceIds[] = $upcoming->id;

                        $adjustedInvoices[] = [
                            'id'     => $upcoming->id,
                            'status' => 'pending',
                            'amount' => $newAmount,
                        ];

                        $credit = 0;
                    }
                }

            } elseif ($credit <= -0.01) {
                // UNDERPAYMENT: add deficit to next invoice only
                $nextInvoice = Invoice::where('contract_id', $invoice->contract_id)
                    ->where('status', 'pending')
                    ->where('due_date', '>', $invoice->due_date)
                    ->orderBy('due_date')
                    ->first();

                if ($nextInvoice) {
                    $newAmount        = round((float) $nextInvoice->amount + abs($credit), 2);
                    $nextInvoice->amount = $newAmount;
                    $nextInvoice->save();

                    $pdfInvoiceIds[] = $nextInvoice->id;

                    $adjustedInvoices[] = [
                        'id'     => $nextInvoice->id,
                        'status' => 'pending',
                        'amount' => $newAmount,
                    ];
                }
            }
        });

        // ── Step 2: Dispatch ALL PDF jobs in one batch after DB is done ───
        // All invoice PDFs (current + any adjusted) are queued together.
        // The receipt + email for the CURRENT invoice are chained after.
        //
        // With QUEUE_CONNECTION=database (recommended) these all run in the
        // background and the HTTP response returns immediately.
        // With QUEUE_CONNECTION=sync they run here, but at least all DB
        // work finished first so the data is always correct.
        DB::afterCommit(function () use ($pdfInvoiceIds, $autoPaidInvoiceIds, $userId, $invoice) {
            // Regenerate all affected invoice PDFs in one batch
            $pdfJobs = array_map(
                fn($id) => new GenerateInvoicePdfJob($id),
                $pdfInvoiceIds
            );

            Bus::batch($pdfJobs)
                ->name("Invoice PDFs after payment #{$invoice->id}")
                ->dispatch();

            // Receipt + email for the manually paid invoice
            Bus::chain([
                new GenerateReceiptPdfJob($invoice->id, $userId),
                new SendInvoiceReceiptMailJob($invoice->id),
            ])->dispatch();

            // Receipts for every auto-paid invoice
            foreach ($autoPaidInvoiceIds as $autoPaidId) {
                GenerateReceiptPdfJob::dispatch($autoPaidId, $userId);
            }
        });

        $audit->details = 'Marking invoice (' . $invoice->invoice_number . ') as paid succeeded. Amount paid: $' . $amountPaid;
        $audit->save();

        return response()->json([
            'message'           => 'Marked as paid',
            'adjusted_invoices' => $adjustedInvoices,
        ]);
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