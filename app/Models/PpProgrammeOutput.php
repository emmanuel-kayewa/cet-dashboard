<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpProgrammeOutput extends Model
{
    protected $fillable = [
        'output_code',
        'programme',
        'period',
        'connections_delivered',
        'transformers_energised',
        'jobs_pending_connection',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'connections_delivered'    => 'integer',
        'transformers_energised'   => 'integer',
        'jobs_pending_connection'  => 'integer',
    ];

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
