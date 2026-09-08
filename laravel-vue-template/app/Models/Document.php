<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'certification_type_id',
        'certificate_number',
        'implementation_date',
        'issued_date',
        'expiry_date',
        'file_path',
        'file_name',
        'file_size',
        'file_mime',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'implementation_date' => 'date',
            'issued_date' => 'date',
            'expiry_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------
    // Status constants
    // -------------------------------------------------------

    const STATUS_PENDING = 'pending_approval';

    const STATUS_DITOLAK = 'ditolak';

    const STATUS_AKTIF = 'aktif';

    const STATUS_SEGERA_EXPIRED = 'segera_expired';

    const STATUS_EXPIRED = 'expired';

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Hitung status berdasarkan expiry_date.
     * Hanya dipakai setelah dokumen di-approve.
     */
    public function computeStatus(): string
    {
        $daysUntilExpiry = Carbon::today()->diffInDays($this->expiry_date, false);

        if ($daysUntilExpiry < 0) {
            return self::STATUS_EXPIRED;
        }

        if ($daysUntilExpiry <= 60) {
            return self::STATUS_SEGERA_EXPIRED;
        }

        return self::STATUS_AKTIF;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [
            self::STATUS_AKTIF,
            self::STATUS_SEGERA_EXPIRED,
            self::STATUS_EXPIRED,
        ]);
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------

    /**
     * Pegawai pemilik dokumen.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Jenis sertifikasi dokumen ini.
     */
    public function certificationType(): BelongsTo
    {
        return $this->belongsTo(CertificationType::class, 'certification_type_id');
    }

    /**
     * User yang mengupload dokumen (bisa admin atau pegawai).
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Admin yang approve atau tolak dokumen ini.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Riwayat file dokumen yang pernah diganti.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    /**
     * Notifikasi yang terkait dengan dokumen ini.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Log reminder yang sudah terkirim untuk dokumen ini.
     */
    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }
}
