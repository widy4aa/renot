<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationType extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kategori induk (HSSE atau Aviasi).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CertificationCategory::class, 'category_id');
    }

    /**
     * Semua dokumen dengan jenis sertifikasi ini.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'certification_type_id');
    }
}
