<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Receipt Voucher - {$this->invoice->invoice_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-receipt',
            with: ['invoice' => $this->invoice],
        );
    }

    public function attachments(): array
    {
        if (!$this->invoice->receipt_path) return [];
        if (!Storage::disk('public')->exists($this->invoice->receipt_path)) return [];

        return [
            Attachment::fromPath(Storage::disk('public')->path($this->invoice->receipt_path))
                ->as("Receipt-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}