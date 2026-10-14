<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationMail;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /**
     * Daftar semua pendaftaran yang menunggu persetujuan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('registration_status', User::REGISTRATION_PENDING)
            ->with('department:id,name')
            ->orderByDesc('registered_at');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('employee_number', 'ilike', "%{$search}%");
            });
        }

        $registrations = $query->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_number' => $user->employee_number,
            'phone' => $user->phone,
            'department' => $user->department?->only('id', 'name'),
            'registered_at' => $user->registered_at?->toISOString(),
        ]);

        return response()->json([
            'data' => $registrations,
            'total' => $registrations->count(),
        ]);
    }

    /**
     * Setujui pendaftaran pegawai.
     */
    public function approve(Request $request, User $user): JsonResponse
    {
        if ($user->registration_status !== User::REGISTRATION_PENDING) {
            return response()->json([
                'message' => 'Pendaftaran ini bukan berstatus pending.',
            ], 422);
        }

        $user->registration_status = User::REGISTRATION_APPROVED;
        $user->is_active = true;
        $user->save();

        // Notifikasi in-app untuk pegawai (akan terlihat saat mereka login)
        Notification::create([
            'user_id' => $user->id,
            'type' => 'registrasi_approved',
            'channel' => 'in_app',
            'title' => 'Pendaftaran Disetujui',
            'body' => 'Selamat! Akun Anda telah disetujui. Anda sekarang dapat menggunakan semua fitur ReNot.',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        // Kirim email notifikasi ke pegawai
        try {
            Mail::to($user->email)->send(new RegistrationMail('registrasi_approved', $user));
        } catch (\Throwable $e) {
            // Lanjut meski email gagal
        }

        // Catat audit trail
        $admin = $request->user();
        ActivityLog::record(
            activityType: 'registrasi_approve',
            userId: $admin->id,
            subjectType: 'User',
            subjectId: $user->id,
            description: "Admin {$admin->name} menyetujui pendaftaran {$user->name}",
        );

        return response()->json([
            'message' => "Pendaftaran {$user->name} berhasil disetujui.",
        ]);
    }

    /**
     * Tolak pendaftaran pegawai.
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($user->registration_status !== User::REGISTRATION_PENDING) {
            return response()->json([
                'message' => 'Pendaftaran ini bukan berstatus pending.',
            ], 422);
        }

        $user->registration_status = User::REGISTRATION_REJECTED;
        $user->registration_rejected_reason = $validated['rejection_reason'];
        $user->save();

        // Kirim email notifikasi penolakan ke pegawai
        try {
            Mail::to($user->email)->send(new RegistrationMail('registrasi_rejected', $user));
        } catch (\Throwable $e) {
            // Lanjut meski email gagal
        }

        // Catat audit trail
        $admin = $request->user();
        ActivityLog::record(
            activityType: 'registrasi_reject',
            userId: $admin->id,
            subjectType: 'User',
            subjectId: $user->id,
            description: "Admin {$admin->name} menolak pendaftaran {$user->name}: {$validated['rejection_reason']}",
        );

        return response()->json([
            'message' => "Pendaftaran {$user->name} telah ditolak.",
        ]);
    }
}
