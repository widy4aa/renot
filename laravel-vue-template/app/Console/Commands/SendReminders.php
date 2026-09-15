<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\PengaturanController;
use App\Mail\ReminderMail;
use App\Models\Document;
use App\Models\Notification;
use App\Models\ReminderLog;
use App\Models\ReminderSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Kirim reminder notifikasi in-app dan email untuk dokumen yang akan kadaluarsa dan update status expired.';

    public function handle(): int
    {
        // Terapkan konfigurasi SMTP dari DB sebelum mulai kirim email
        PengaturanController::applySmtpToRuntime();

        $today = Carbon::today();
        $schedules = ReminderSchedule::where('is_active', true)->get();

        $sent = 0;
        $updated = 0;

        // 1. Update dokumen approved yang sudah melewati expiry_date → expired
        $expiredDocs = Document::whereIn('status', [
            Document::STATUS_AKTIF,
            Document::STATUS_SEGERA_EXPIRED,
        ])->whereDate('expiry_date', '<', $today)->with(['owner.department', 'certificationType'])->get();

        foreach ($expiredDocs as $doc) {
            $doc->update(['status' => Document::STATUS_EXPIRED]);

            Notification::create([
                'user_id' => $doc->user_id,
                'channel' => 'in_app',
                'type' => 'dokumen_expired',
                'title' => 'Dokumen Kadaluarsa',
                'body' => "Dokumen sertifikasi {$doc->certificationType?->name} Anda telah kadaluarsa pada {$doc->expiry_date->format('d/m/Y')}. Segera perbarui.",
                'document_id' => $doc->id,
                'is_read' => false,
            ]);

            // Kirim email notifikasi expired
            $this->sendEmail($doc, 'dokumen_expired');

            $updated++;
        }

        // 2. Kirim reminder berdasarkan jadwal H-X
        foreach ($schedules as $schedule) {
            $targetDate = $today->copy()->addDays($schedule->days_before);

            $docs = Document::whereIn('status', [
                Document::STATUS_AKTIF,
                Document::STATUS_SEGERA_EXPIRED,
            ])->whereDate('expiry_date', $targetDate)->with(['owner.department', 'certificationType'])->get();

            foreach ($docs as $doc) {
                // Cek deduplication
                $alreadySent = ReminderLog::where('document_id', $doc->id)
                    ->where('reminder_schedule_id', $schedule->id)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Kirim notifikasi in-app ke pegawai
                Notification::create([
                    'user_id' => $doc->user_id,
                    'channel' => 'in_app',
                    'type' => 'reminder_akan_expired',
                    'title' => "Reminder: Sertifikasi Akan Kadaluarsa dalam {$schedule->days_before} Hari",
                    'body' => "Dokumen {$doc->certificationType?->name} Anda akan kadaluarsa pada {$doc->expiry_date->format('d/m/Y')}. Segera persiapkan pembaruan.",
                    'document_id' => $doc->id,
                    'is_read' => false,
                ]);

                // Kirim email reminder H-X
                $this->sendEmail($doc, 'reminder_akan_expired', [
                    '{{hari_tersisa}}' => (string) $schedule->days_before,
                ]);

                // Catat ke reminder_logs
                ReminderLog::create([
                    'document_id' => $doc->id,
                    'reminder_schedule_id' => $schedule->id,
                ]);

                $sent++;
            }
        }

        $this->info("Selesai. {$sent} reminder dikirim, {$updated} dokumen diupdate ke expired.");

        return self::SUCCESS;
    }

    /**
     * Kirim email ke pemilik dokumen berdasarkan tipe template.
     * Gagal secara silent — dicatat ke log, command tetap lanjut.
     *
     * @param  array<string, string>  $extraPlaceholders
     */
    private function sendEmail(Document $doc, string $type, array $extraPlaceholders = []): void
    {
        $owner = $doc->relationLoaded('owner') ? $doc->owner : $doc->owner()->first();

        if (! $owner || ! $owner->email) {
            return;
        }

        try {
            Mail::to($owner->email)->send(new ReminderMail($type, $doc, $extraPlaceholders));
        } catch (\Throwable $e) {
            Log::error("[ReNot] Gagal kirim email {$type} ke {$owner->email} untuk dokumen ID {$doc->id}: {$e->getMessage()}");
        }
    }
}
