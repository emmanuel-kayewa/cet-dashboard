<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WayleaveEntry extends Model
{
    protected $fillable = [
        'directorate_id',
        'category',
        'aspect',
        'received',
        'cleared',
        'report_date',
        'notes',
        'source',
        'entered_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'received' => 'integer',
        'cleared' => 'integer',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
