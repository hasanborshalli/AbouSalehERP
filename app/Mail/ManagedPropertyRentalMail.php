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
class ManagedPropertyRentalMail extends Mailable
{
use Queueable, SerializesModels;

public function __construct(
public ManagedProperty $property,
public \App\Models\ManagedPropertyRental $rental
) {}

public function envelope(): Envelope
{
return new Envelope(
subject: "Rental Contract — {$this->property->address}"
);
}

public function content(): Content
{
return new Content(
view: 'emails.managed-rental',
with: [
'property' => $this->property,
'rental' => $this->rental,
],
);
}

public function attachments(): array
{
if (!$this->rental->pdf_path) return [];
if (!Storage::disk('public')->exists($this->rental->pdf_path)) return [];

return [
Attachment::fromPath(Storage::disk('public')->path($this->rental->pdf_path))
->as("RentalContract-RC-{$this->rental->id}.pdf")
->withMime('application/pdf'),
];
}
}