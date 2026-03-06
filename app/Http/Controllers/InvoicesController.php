<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InventoryItem;
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
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Invoice';
        $audit->details='Updating invoice date ('.$invoice->invoice_number.') failed';
        $audit->save();
        $audit->record='INV-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        $data = $request->validate([
            'issue_date' => ['nullable','date'],
            'due_date'   => ['nullable','date','after_or_equal:issue_date'],
        ]);
        
        $invoice->update([
            'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
            'due_date'   => $data['due_date'] ?? $invoice->due_date,
        ]);

        GenerateInvoicePdfJob::dispatchSync($invoice->id);
        $audit->details='Updating invoice date ('.$invoice->invoice_number.') succeeded';
        $audit->save();
        return response()->json(['message' => 'Dates updated + PDF regenerated']);
    }

    public function markPaid(Request $request, Invoice $invoice, CashAccountingService $acct)
    {
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Invoice';
        $audit->details='Marking invoice ('.$invoice->invoice_number.') as paid failed';
        $audit->save();
        $audit->record='INV-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();

        $paymentType = $request->input('payment_type', 'cash');

        // ── IN-KIND PAYMENT PATH ──────────────────────────────────────────
        if ($paymentType === 'in_kind') {
            $data = $request->validate([
                'items'                       => ['required', 'array', 'min:1'],
                'items.*.inventory_item_id'   => ['required', 'integer', 'exists:inventory_items,id'],
                'items.*.quantity_used'       => ['required', 'numeric', 'min:0.001'],
                'items.*.notes'               => ['nullable', 'string', 'max:255'],
            ]);

            $invoiceTotal = (float)$invoice->amount + (float)$invoice->late_fee_amount;
            $runningTotal = 0;
            $enrichedItems = [];

            foreach ($data['items'] as $row) {
                $item = InventoryItem::findOrFail($row['inventory_item_id']);

                if ((float)$item->quantity < (float)$row['quantity_used']) {
                    return response()->json([
                        'message' => "Insufficient stock for \"{$item->name}\". Available: {$item->quantity} {$item->unit}."
                    ], 422);
                }

                $unitPrice  = (float)$item->price;
                $rowValue   = round($unitPrice * (float)$row['quantity_used'], 2);
                $runningTotal += $rowValue;

                $enrichedItems[] = [
                    'inventory_item_id' => $item->id,
                    'quantity_used'     => (float)$row['quantity_used'],
                    'unit_price'        => $unitPrice,
                    'total_value'       => $rowValue,
                    'notes'             => $row['notes'] ?? null,
                ];
            }

            // Allow $1 tolerance for rounding
            if (abs($runningTotal - $invoiceTotal) > 1.00) {
                return response()->json([
                    'message' => "Items total (\$" . number_format($runningTotal,2) . ") does not match invoice amount (\$" . number_format($invoiceTotal,2) . "). Please adjust quantities."
                ], 422);
            }

            DB::transaction(function () use ($invoice, $enrichedItems, $acct) {
                $invoice->status       = 'paid';
                $invoice->payment_type = 'in_kind';
                $invoice->paid_at      = now();
                $invoice->save();

                $acct->postInvoicePaidInKind($invoice, $enrichedItems, auth()->id());

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

            GenerateInvoicePdfJob::dispatch($invoice->id);

            DB::afterCommit(function () use ($invoice) {
                Bus::chain([
                    new GenerateReceiptPdfJob($invoice->id, auth()->id()),
                    new SendInvoiceReceiptMailJob($invoice->id),
                ])->dispatch();
            });

            $audit->details = 'Marking invoice ('.$invoice->invoice_number.') as paid (in-kind) succeeded';
            $audit->save();

            return response()->json(['message' => 'Marked as paid (in-kind)']);
        }

        // ── CASH PAYMENT PATH (original) ──────────────────────────────────
        $data = $request->validate([
            'paid_at' => ['nullable','date'],
        ]);

        $invoice->status       = 'paid';
        $invoice->payment_type = 'cash';
        $paidAt = $invoice->paid_at ? Carbon::parse($invoice->paid_at) : now();
        $invoice->paid_at = $paidAt;
        $invoice->save();

        GenerateInvoicePdfJob::dispatch($invoice->id);

        $acct->postInvoicePaid($invoice, $paidAt, auth()->id());

        $invoice->loadMissing('contract.apartment');
        $contract  = $invoice->contract;
        $apartment = $contract?->apartment;

        if ($apartment && $apartment->status !== 'sold') {
            $paidCount = $contract->invoices()
                ->where('status', 'paid')
                ->count();

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

        $audit->details='Marking invoice ('.$invoice->invoice_number.') as paid succeeded';
        $audit->save();
        return response()->json(['message' => 'Marked as paid']);
    }

    /**
     * Return active inventory items as JSON (for the in-kind payment picker).
     */
    public function inventoryItemsJson()
    {
        $items = InventoryItem::whereNull('deleted_at')
            ->where('quantity', '>', 0)
            ->select('id', 'name', 'price', 'quantity', 'unit')
            ->orderBy('name')
            ->get();

        return response()->json($items);
    }
}