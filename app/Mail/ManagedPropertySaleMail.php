<?php

namespace App\Mail;

use App\Models\ManagedProperty;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagedPropertySaleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ManagedProperty $property,
        public \App\Models\ManagedPropertySale $sale
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Sale Confirmation — {$this->property->address}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.managed-sale',
            with: [
                'property' => $this->property,
                'sale'     => $this->sale,
            ],
        );
    }

    public function attachments(): array { return []; }
}