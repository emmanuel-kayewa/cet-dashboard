<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationLog extends Model
{
    protected $fillable = [
        'event_type',
        'directorate_id',
        'data',
        'previous_data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'previous_data' => 'array',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }
}
