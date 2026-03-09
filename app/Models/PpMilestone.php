<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpMilestone extends Model
{
    protected $fillable = [
        'milestone_code',
        'pp_project_id',
        'milestone',
        'actual_date',
        'status',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'actual_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(PpProject::class, 'pp_project_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
