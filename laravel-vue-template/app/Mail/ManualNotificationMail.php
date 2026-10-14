<?php

namespace App\Mail;

use App\Http\Controllers\Admin\PengaturanController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManualNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedBody;

    /**
     * Buat instance email notifikasi manual dari admin.
     */
    public function __construct(
        public readonly string $emailSubject,
        public readonly string $emailBody,
        public readonly string $recipientName,
    ) {
        PengaturanController::applySmtpToRuntime();

        // Replace placeholder nama jika admin menggunakan {{nama}} di body
        $this->renderedBody = str_replace('{{nama}}', $recipientName, $emailBody);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.template',
            with: [
                'body' => $this->renderedBody,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
