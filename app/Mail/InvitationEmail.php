<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $qr_code_file;

    /**
     * Create a new message instance.
     */
    public function __construct(public Invitation $invitation)
    {
        $qr_code = QrCode::size(300)->margin(4)->format('png')->generate($invitation->key);
        $this->qr_code_file = tempnam(sys_get_temp_dir(), 'qr_code');
        file_put_contents($this->qr_code_file, $qr_code);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to ' . $this->invitation->event()->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
