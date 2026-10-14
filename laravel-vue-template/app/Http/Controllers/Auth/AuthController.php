<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi salah.'],
            ]);
        }

        if (! $user->is_active) {
            if ($user->registration_status === User::REGISTRATION_PENDING) {
                throw ValidationException::withMessages([
                    'email' => ['Akun Anda masih menunggu persetujuan admin.'],
                ]);
            }

            if ($user->registration_status === User::REGISTRATION_REJECTED) {
                throw ValidationException::withMessages([
                    'email' => ['Pendaftaran Anda ditolak. Silakan daftar ulang atau hubungi admin.'],
                ]);
            }

            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Hubungi administrator.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email',
                Rule::unique('users', 'email')->where(function ($query) {
                    $query->where('registration_status', '!=', User::REGISTRATION_REJECTED);
                }),
            ],
            'employee_number' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'employee_number')->where(function ($query) {
                    $query->where('registration_status', '!=', User::REGISTRATION_REJECTED);
                }),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Hapus pendaftaran yang sudah ditolak dengan email/NIP yang sama
        User::where('registration_status', User::REGISTRATION_REJECTED)
            ->where(function ($q) use ($validated) {
                $q->where('email', $validated['email'])
                    ->orWhere('employee_number', $validated['employee_number']);
            })
            ->delete();

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_number' => $validated['employee_number'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'],
            'password' => $validated['password'],
            'registered_at' => now(),
        ]);
        $user->role = 'pegawai';
        $user->is_active = false;
        $user->registration_status = User::REGISTRATION_PENDING;
        $user->save();

        // Kirim notifikasi in-app ke semua admin aktif
        $admins = User::where('role', 'admin')
            ->where('is_active', true)
            ->where('registration_status', User::REGISTRATION_APPROVED)
            ->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'registrasi_baru',
                'channel' => 'in_app',
                'title' => 'Pendaftaran Baru',
                'body' => "{$user->name} mendaftar sebagai pegawai baru dan menunggu persetujuan.",
                'sent_at' => now(),
                'created_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Pendaftaran berhasil. Silakan tunggu persetujuan admin.',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('department');

        return response()->json([
            ...$this->formatUser($user),
            'unread_notifications_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login.',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'employee_number' => $user->employee_number,
            'phone' => $user->phone,
            'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
            'department' => $user->department?->only('id', 'name'),
        ];
    }
}
