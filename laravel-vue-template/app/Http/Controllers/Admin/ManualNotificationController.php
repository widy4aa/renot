<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ManualNotificationMail;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ManualNotificationController extends Controller
{
    /**
     * Kirim email manual ke pegawai tertentu.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->role !== 'pegawai') {
            return response()->json([
                'message' => 'Hanya bisa mengirim notifikasi ke pegawai.',
            ], 422);
        }

        // Kirim email
        try {
            Mail::to($user->email)->send(new ManualNotificationMail(
                emailSubject: $validated['subject'],
                emailBody: $validated['body'],
                recipientName: $user->name,
            ));
        } catch (\Throwable $e) {
            \Log::error('Manual notification failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);

            return response()->json([
                'message' => 'Gagal mengirim email. Periksa konfigurasi SMTP di Pengaturan Sistem.',
            ], 500);
        }

        // Buat notifikasi in-app
        Notification::create([
            'user_id' => $user->id,
            'type' => 'notifikasi_manual',
            'channel' => 'in_app',
            'title' => $validated['subject'],
            'body' => $validated['body'],
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        // Catat audit trail
        $admin = $request->user();
        ActivityLog::record(
            activityType: 'notifikasi_manual',
            userId: $admin->id,
            subjectType: 'User',
            subjectId: $user->id,
            description: "Admin {$admin->name} mengirim email manual ke {$user->name}: {$validated['subject']}",
        );

        return response()->json([
            'message' => "Email berhasil dikirim ke {$user->name}.",
        ]);
    }
}
