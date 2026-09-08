<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'reminder_schedule_id',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Dokumen yang dikirimi reminder.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Jadwal reminder yang dipakai.
     */
    public function reminderSchedule(): BelongsTo
    {
        return $this->belongsTo(ReminderSchedule::class);
    }
}
