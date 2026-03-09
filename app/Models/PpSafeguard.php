<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpSafeguard extends Model
{
    protected $fillable = [
        'record_code',
        'scope',
        'pp_project_id',
        'wayleave_received',
        'wayleave_cleared',
        'wayleave_pending',
        'survey_received',
        'survey_cleared',
        'survey_pending',
        'paps',
        'comp_paid_zmw',
        'report_period',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'wayleave_received' => 'integer',
        'wayleave_cleared'  => 'integer',
        'wayleave_pending'  => 'integer',
        'survey_received'   => 'integer',
        'survey_cleared'    => 'integer',
        'survey_pending'    => 'integer',
        'paps'              => 'integer',
        'comp_paid_zmw'     => 'decimal:2',
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
