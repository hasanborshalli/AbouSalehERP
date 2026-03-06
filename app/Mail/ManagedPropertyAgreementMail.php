<?php
namespace App\Mail;

use App\Models\ManagedProperty;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ManagedPropertyAgreementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ManagedProperty $property) {}

    public function envelope(): Envelope
    {
        $type = $this->property->isFlip() ? 'Flip / Sale' : 'Rental Management';
        return new Envelope(
            subject: "Property Management Agreement — {$type} — {$this->property->address}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.managed-agreement',
            with: ['property' => $this->property],
        );
    }

    public function attachments(): array
    {
        if (!$this->property->pdf_path) return [];
        if (!Storage::disk('public')->exists($this->property->pdf_path)) return [];

        return [
            Attachment::fromPath(Storage::disk('public')->path($this->property->pdf_path))
                ->as("Agreement-MP-{$this->property->id}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}