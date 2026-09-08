<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderSchedule extends Model
{
    protected $fillable = [
        'days_before',
        'urgency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'days_before' => 'integer',
        ];
    }

    /**
     * Log pengiriman reminder berdasarkan jadwal ini.
     */
    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }
}
