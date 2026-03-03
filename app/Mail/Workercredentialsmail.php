<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WorkerCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $rawPassword,
        public ?string $contractPath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Worker Portal Access – Abou Saleh');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.worker-credentials');
    }

    public function attachments(): array
    {
        if (!$this->contractPath) return [];
        if (!Storage::disk('public')->exists($this->contractPath)) return [];

        return [
            Attachment::fromPath(Storage::disk('public')->path($this->contractPath))
                ->as('WorkerContract.pdf')
                ->withMime('application/pdf'),
        ];
    }
}