<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'file_path',
        'file_name',
        'file_size',
        'file_mime',
        'replaced_by',
        'replaced_at',
    ];

    protected function casts(): array
    {
        return [
            'replaced_at' => 'datetime',
        ];
    }

    /**
     * Dokumen induk yang versi ini miliknya.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * User yang mengganti file (trigger pembuatan versi ini).
     */
    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }
}
