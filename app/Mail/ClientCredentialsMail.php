<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;


class ClientCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels; 
    public User $user;
    public string $rawPassword;
    public ?string $contractPath;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $rawPassword,?string $contractPath = null)
    {
        $this->user = $user;
        $this->rawPassword = $rawPassword;
        $this->contractPath = $contractPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Client Credentials Mail',
        );
    }

    /**
     * Get the message content definition.
     */
  public function content(): Content
{
    return new Content(
        view: 'emails.client-credentials',
        with: [
            'user' => $this->user,
            'rawPassword' => $this->rawPassword,
            'contractPath' => $this->contractPath,
        ],
    );
}


    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
         if (!$this->contractPath) {
        return [];
    }

    // optional: only attach if file exists
    if (!Storage::disk('public')->exists($this->contractPath)) {
        return [];
    }

    return [
        Attachment::fromPath(Storage::disk('public')->path($this->contractPath))
            ->as('Contract.pdf')
            ->withMime('application/pdf'),
    ];
    }
}