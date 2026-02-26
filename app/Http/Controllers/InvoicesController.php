<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Services\CashAccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\GenerateReceiptPdfJob;
use App\Jobs\SendInvoiceReceiptMailJob;
use Illuminate\Support\Facades\Bus;
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
        $invoice->status = 'paid';

    $paidAt = $invoice->paid_at ? Carbon::parse($invoice->paid_at) : now();
    $invoice->paid_at=$paidAt;
        $invoice->save();
        
        GenerateInvoicePdfJob::dispatch($invoice->id); 
        // Cash-basis: post revenue now
$acct->postInvoicePaid($invoice, $paidAt, auth()->id());
 // ✅ after commit: generate receipt PDF then email it
    \DB::afterCommit(function () use ($invoice) {
        Bus::chain([
            new GenerateReceiptPdfJob($invoice->id, auth()->id()),
            new SendInvoiceReceiptMailJob($invoice->id),
        ])->dispatch();
    });
    $audit->details='Marking invoice ('.$invoice->invoice_number.') as paid succeeded';
        $audit->save();
        return response()->json(['message' => 'Marked as paid']);
    }
}