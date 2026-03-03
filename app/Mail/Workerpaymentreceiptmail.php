<?php

namespace App\Mail;

use App\Models\WorkerPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WorkerPaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public WorkerPayment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Receipt – {$this->payment->payment_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.worker-payment-receipt',
            with: ['payment' => $this->payment],
        );
    }

    public function attachments(): array
    {
        if (!$this->payment->receipt_path) return [];
        if (!Storage::disk('public')->exists($this->payment->receipt_path)) return [];

        return [
            Attachment::fromPath(Storage::disk('public')->path($this->payment->receipt_path))
                ->as("Receipt-{$this->payment->payment_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}