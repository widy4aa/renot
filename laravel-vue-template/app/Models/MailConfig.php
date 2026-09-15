<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailConfig extends Model
{
    protected $fillable = [
        'name',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mail_port' => 'integer',
            'is_active' => 'boolean',
            // Password disimpan terenkripsi di DB
            'mail_password' => 'encrypted',
        ];
    }

    /**
     * Ambil profile yang sedang aktif.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Set profile ini sebagai aktif — nonaktifkan semua yang lain.
     */
    public function activate(): void
    {
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Admin yang membuat profile ini.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
