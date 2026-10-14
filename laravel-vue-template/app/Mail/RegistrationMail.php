<?php

namespace App\Mail;

use App\Http\Controllers\Admin\PengaturanController;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;

    public string $renderedBody;

    /**
     * Buat instance email untuk notifikasi registrasi.
     *
     * @param  string  $type  Tipe template: 'registrasi_approved' atau 'registrasi_rejected'
     * @param  User  $user  User yang mendaftar
     */
    public function __construct(
        public readonly string $type,
        public readonly User $user,
    ) {
        PengaturanController::applySmtpToRuntime();

        $template = EmailTemplate::where('type', $type)->first();

        $subject = $template?->subject ?? 'Notifikasi Pendaftaran ReNot';
        $body = $template?->body ?? 'Notifikasi dari sistem ReNot.';

        $placeholders = $this->buildPlaceholders($user);

        $this->renderedSubject = $this->replacePlaceholders($subject, $placeholders);
        $this->renderedBody = $this->replacePlaceholders($body, $placeholders);
    }

    /**
     * Bangun array placeholder dari data user.
     *
     * @return array<string, string>
     */
    private function buildPlaceholders(User $user): array
    {
        $department = $user->relationLoaded('department')
            ? $user->department
            : $user->department()->first();

        return [
            '{{nama}}' => $user->name,
            '{{email}}' => $user->email,
            '{{nip}}' => $user->employee_number ?? '-',
            '{{departemen}}' => $department?->name ?? '-',
            '{{alasan_penolakan}}' => $user->registration_rejected_reason ?? '-',
        ];
    }

    /**
     * Replace semua placeholder dalam string.
     *
     * @param  array<string, string>  $placeholders
     */
    private function replacePlaceholders(string $text, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject,
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
