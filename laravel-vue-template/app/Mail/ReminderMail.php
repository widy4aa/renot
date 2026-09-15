<?php

namespace App\Mail;

use App\Http\Controllers\Admin\PengaturanController;
use App\Models\Document;
use App\Models\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;

    public string $renderedBody;

    /**
     * Buat instance email dari template DB dan data dokumen.
     *
     * @param  string  $type  Tipe notifikasi, sesuai kolom `type` di tabel email_templates
     * @param  Document  $document  Dokumen yang memicu notifikasi
     * @param  array<string, string>  $extraPlaceholders  Placeholder tambahan (misal: hari_tersisa, alasan_penolakan)
     */
    public function __construct(
        public readonly string $type,
        public readonly Document $document,
        public readonly array $extraPlaceholders = [],
    ) {
        // Terapkan konfigurasi SMTP dari DB (jika sudah dikonfigurasi admin)
        PengaturanController::applySmtpToRuntime();

        $template = EmailTemplate::where('type', $type)->first();

        $subject = $template?->subject ?? 'Notifikasi ReNot';
        $body = $template?->body ?? 'Notifikasi dari sistem ReNot.';

        $placeholders = $this->buildPlaceholders($document, $extraPlaceholders);

        $this->renderedSubject = $this->replacePlaceholders($subject, $placeholders);
        $this->renderedBody = $this->replacePlaceholders($body, $placeholders);
    }

    /**
     * Bangun array placeholder dari data dokumen dan data tambahan.
     *
     * @param  array<string, string>  $extras
     * @return array<string, string>
     */
    private function buildPlaceholders(Document $document, array $extras): array
    {
        $owner = $document->relationLoaded('owner') ? $document->owner : $document->owner()->first();
        $certType = $document->relationLoaded('certificationType') ? $document->certificationType : $document->certificationType()->first();

        $expiryDate = $document->expiry_date instanceof Carbon
            ? $document->expiry_date
            : Carbon::parse($document->expiry_date);

        $daysRemaining = (int) Carbon::today()->diffInDays($expiryDate, false);

        return array_merge([
            '{{nama}}' => $owner?->name ?? '-',
            '{{sertifikasi}}' => $certType?->name ?? '-',
            '{{tanggal_kadaluarsa}}' => $expiryDate->format('d/m/Y'),
            '{{hari_tersisa}}' => $daysRemaining > 0 ? (string) $daysRemaining : '0',
            '{{departemen}}' => $owner?->department?->name ?? '-',
            '{{nomor_sertifikat}}' => $document->certificate_number ?? '-',
        ], $extras);
    }

    /**
     * Replace semua placeholder dalam string dengan nilai nyata.
     *
     * @param  array<string, string>  $placeholders
     */
    private function replacePlaceholders(string $text, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    /**
     * Subject email diambil dari rendered subject template DB.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject,
        );
    }

    /**
     * Konten email menggunakan view Blade sebagai wrapper HTML.
     */
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
     * Lampirkan file dokumen jika ada dan tersedia di storage.
     * Hanya dilampirkan untuk tipe notifikasi yang relevan dengan dokumen itu sendiri
     * (approved, ditolak, expired) — reminder tidak perlu lampiran.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Tipe yang tidak perlu lampiran file
        $skipAttachment = ['reminder_akan_expired', 'dokumen_pending'];

        if (in_array($this->type, $skipAttachment)) {
            return [];
        }

        if (! $this->document->file_path) {
            return [];
        }

        if (! Storage::disk('public')->exists($this->document->file_path)) {
            return [];
        }

        $fullPath = Storage::disk('public')->path($this->document->file_path);
        $displayName = $this->document->file_name ?? basename($this->document->file_path);

        return [
            Attachment::fromPath($fullPath)->as($displayName),
        ];
    }
}
