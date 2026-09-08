<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Admin yang terakhir mengubah pengaturan ini.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Simpan atau update nilai setting.
     */
    public static function setValue(string $key, mixed $value, ?int $userId = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]
        );
    }
}
