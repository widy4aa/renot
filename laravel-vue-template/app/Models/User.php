<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const REGISTRATION_PENDING = 'pending';

    public const REGISTRATION_APPROVED = 'approved';

    public const REGISTRATION_REJECTED = 'rejected';

    protected $fillable = [
        'employee_number',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'department_id',
        'created_by',
        'registration_rejected_reason',
        'registered_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'registered_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isRegistrationPending(): bool
    {
        return $this->registration_status === self::REGISTRATION_PENDING;
    }

    public function isRegistrationApproved(): bool
    {
        return $this->registration_status === self::REGISTRATION_APPROVED;
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    /**
     * Departemen tempat pegawai bertugas.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Admin yang mendaftarkan user ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Pegawai yang didaftarkan oleh admin ini.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Semua dokumen milik pegawai ini.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id');
    }

    /**
     * Dokumen yang diupload oleh user ini (bisa admin atau pegawai).
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * Dokumen yang di-approve/tolak oleh admin ini.
     */
    public function approvedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'approved_by');
    }

    /**
     * Notifikasi yang diterima oleh user ini.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Notifikasi in-app yang belum dibaca.
     */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)
            ->where('channel', 'in_app')
            ->where('is_read', false);
    }

    /**
     * Log aktivitas yang dilakukan oleh user ini.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
