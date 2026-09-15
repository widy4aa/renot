<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\MailConfig;
use App\Models\ReminderSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PengaturanController extends Controller
{
    // ── Reminder Schedules ──────────────────────────────────

    public function reminders(): JsonResponse
    {
        return response()->json([
            'data' => ReminderSchedule::orderBy('days_before', 'desc')->get(),
        ]);
    }

    public function storeReminder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days_before' => ['required', 'integer', 'min:1', 'max:365', 'unique:reminder_schedules,days_before'],
            'urgency' => ['required', 'in:rendah,sedang,tinggi,sangat_tinggi'],
        ]);

        $schedule = ReminderSchedule::create([
            'days_before' => $validated['days_before'],
            'urgency' => $validated['urgency'],
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Jadwal reminder berhasil ditambahkan.', 'schedule' => $schedule], 201);
    }

    public function toggleReminder(ReminderSchedule $schedule): JsonResponse
    {
        $schedule->update(['is_active' => ! $schedule->is_active]);

        return response()->json([
            'message' => 'Status jadwal berhasil diubah.',
            'is_active' => $schedule->is_active,
        ]);
    }

    public function destroyReminder(ReminderSchedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json(['message' => 'Jadwal reminder berhasil dihapus.']);
    }

    // ── Email Templates ─────────────────────────────────────

    public function emailTemplates(): JsonResponse
    {
        return response()->json(['data' => EmailTemplate::all()]);
    }

    public function updateEmailTemplate(Request $request, EmailTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $template->update([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'updated_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Template email berhasil diperbarui.', 'template' => $template]);
    }

    // ── Mail Config Profiles ────────────────────────────────

    /**
     * Daftar semua profile SMTP.
     * Password tidak dikembalikan ke frontend.
     */
    public function mailConfigs(): JsonResponse
    {
        $configs = MailConfig::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($c) => $this->formatMailConfig($c));

        return response()->json(['data' => $configs]);
    }

    /**
     * Tambah profile SMTP baru.
     */
    public function storeMailConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'in:25,465,587,2525'],
            'mail_username' => ['required', 'email', 'max:255'],
            'mail_password' => ['required', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:100'],
        ]);

        $config = MailConfig::create([
            ...$validated,
            'is_active' => false,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Profile email berhasil ditambahkan.',
            'config' => $this->formatMailConfig($config),
        ], 201);
    }

    /**
     * Update profile SMTP.
     * Kalau password dikosongkan, password lama tetap dipakai.
     */
    public function updateMailConfig(Request $request, MailConfig $mailConfig): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'in:25,465,587,2525'],
            'mail_username' => ['required', 'email', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:100'],
        ]);

        // Jika password dikosongkan, hapus dari array agar tidak overwrite
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        $mailConfig->update($validated);

        return response()->json([
            'message' => 'Profile email berhasil diperbarui.',
            'config' => $this->formatMailConfig($mailConfig->fresh()),
        ]);
    }

    /**
     * Hapus profile SMTP.
     * Profile yang sedang aktif tidak boleh dihapus.
     */
    public function destroyMailConfig(MailConfig $mailConfig): JsonResponse
    {
        if ($mailConfig->is_active) {
            return response()->json([
                'message' => 'Profile yang sedang aktif tidak dapat dihapus. Aktifkan profile lain terlebih dahulu.',
            ], 422);
        }

        $mailConfig->delete();

        return response()->json(['message' => 'Profile email berhasil dihapus.']);
    }

    /**
     * Set profile ini sebagai aktif — nonaktifkan semua yang lain.
     */
    public function activateMailConfig(MailConfig $mailConfig): JsonResponse
    {
        $mailConfig->activate();

        return response()->json([
            'message' => "Profile \"{$mailConfig->name}\" sekarang aktif.",
            'config' => $this->formatMailConfig($mailConfig->fresh()),
        ]);
    }

    /**
     * Test kirim email menggunakan config dari profile tertentu (tanpa harus mengaktifkannya).
     */
    public function testMailConfig(Request $request, MailConfig $mailConfig): JsonResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        // Override runtime config sementara dengan profile yang di-test
        $this->applyConfigToRuntime($mailConfig);

        try {
            Mail::raw(
                "Halo!\n\nIni adalah email uji coba dari sistem ReNot menggunakan profile \"{$mailConfig->name}\".\nJika Anda menerima email ini, berarti konfigurasi SMTP berhasil.\n\nHost : {$mailConfig->mail_host}:{$mailConfig->mail_port}\nFrom : {$mailConfig->mail_from_address}\n\n— Tim ReNot",
                fn ($m) => $m
                    ->to($validated['test_email'])
                    ->subject("[ReNot] Test Profile \"{$mailConfig->name}\" Berhasil"),
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal: '.$e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => "Email uji coba berhasil dikirim ke {$validated['test_email']} menggunakan profile \"{$mailConfig->name}\".",
        ]);
    }

    /**
     * Format data profile untuk response — password tidak dikembalikan.
     *
     * @return array<string, mixed>
     */
    private function formatMailConfig(MailConfig $config): array
    {
        return [
            'id' => $config->id,
            'name' => $config->name,
            'mail_host' => $config->mail_host,
            'mail_port' => $config->mail_port,
            'mail_username' => $config->mail_username,
            'mail_from_address' => $config->mail_from_address,
            'mail_from_name' => $config->mail_from_name,
            'is_active' => $config->is_active,
            'created_at' => $config->created_at?->format('d/m/Y'),
        ];
    }

    /**
     * Terapkan konfigurasi satu MailConfig ke Laravel runtime config.
     */
    private function applyConfigToRuntime(MailConfig $config): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailer' => 'smtp',
            'mail.mailers.smtp.host' => $config->mail_host,
            'mail.mailers.smtp.port' => $config->mail_port,
            'mail.mailers.smtp.username' => $config->mail_username,
            'mail.mailers.smtp.password' => $config->mail_password,
            'mail.mailers.smtp.scheme' => null,
            'mail.from.address' => $config->mail_from_address,
            'mail.from.name' => $config->mail_from_name,
        ]);
    }

    /**
     * Terapkan profile SMTP yang aktif dari DB ke Laravel config runtime.
     * Dipanggil setiap kali sistem hendak mengirim email.
     * Jika tidak ada profile aktif di DB, fallback ke .env.
     */
    public static function applySmtpToRuntime(): void
    {
        $active = MailConfig::active();

        if (! $active) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailer' => 'smtp',
            'mail.mailers.smtp.host' => $active->mail_host,
            'mail.mailers.smtp.port' => $active->mail_port,
            'mail.mailers.smtp.username' => $active->mail_username,
            'mail.mailers.smtp.password' => $active->mail_password,
            'mail.mailers.smtp.scheme' => null,
            'mail.from.address' => $active->mail_from_address,
            'mail.from.name' => $active->mail_from_name,
        ]);
    }
}
