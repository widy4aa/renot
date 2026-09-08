<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificationCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_deletable',
    ];

    protected function casts(): array
    {
        return [
            'is_deletable' => 'boolean',
        ];
    }

    /**
     * Jenis-jenis sertifikasi dalam kategori ini.
     */
    public function types(): HasMany
    {
        return $this->hasMany(CertificationType::class, 'category_id');
    }

    /**
     * Jenis sertifikasi yang masih aktif.
     */
    public function activeTypes(): HasMany
    {
        return $this->hasMany(CertificationType::class, 'category_id')
            ->where('is_active', true);
    }
}
