<?php

namespace App\Jobs;

use App\Mail\InvoiceReceiptMail;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReceiptMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $invoiceId) {}

    public function handle(): void
    {
        $invoice = Invoice::with('contract.client')->findOrFail($this->invoiceId);

        $clientEmail = $invoice->contract?->client?->email;
        if (!$clientEmail) return;

        Mail::to($clientEmail)->send(new InvoiceReceiptMail($invoice));
    }
}