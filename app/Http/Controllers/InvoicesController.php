<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Services\CashAccountingService;
use Illuminate\Http\Request;

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

         // ✅ regenerate PDF now (same request)
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
        $data = $request->validate([
            'paid_at' => ['nullable','date'],
        ]);

        // if you have a paid_at column, store it.
        // if not, ignore it.
        $invoice->status = 'paid';

        if ($invoice->isFillable('paid_at') && array_key_exists('paid_at', $data)) {
            $invoice->paid_at = $data['paid_at'];
        }

        $invoice->save();
        GenerateInvoicePdfJob::dispatchSync($invoice->id);
        $audit->details='Marking invoice ('.$invoice->invoice_number.') as paid succeeded';
        $audit->save();
        // Cash-basis: post revenue now
    $acct->postInvoicePaid($invoice, now(), auth()->id());
        return response()->json(['message' => 'Marked as paid']);
    }
}