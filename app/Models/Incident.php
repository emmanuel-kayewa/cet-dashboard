<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'directorate_id',
        'title',
        'description',
        'type',
        'severity',
        'status',
        'root_cause',
        'resolution',
        'lessons_learned',
        'affected_area',
        'affected_customers',
        'occurred_at',
        'resolved_at',
        'source',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'affected_customers' => 'integer',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'amber',
            'low' => 'green',
            default => 'gray',
        };
    }

    public function getResolutionTimeAttribute(): ?string
    {
        if (!$this->occurred_at || !$this->resolved_at) {
            return null;
        }

        $diff = $this->occurred_at->diff($this->resolved_at);

        if ($diff->days > 0) {
            return $diff->days . 'd ' . $diff->h . 'h';
        }

        return $diff->h . 'h ' . $diff->i . 'm';
    }
}
