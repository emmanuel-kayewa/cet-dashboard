<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'title',
        'message',
        'directorate_id',
        'metadata',
        'is_read',
        'is_dismissed',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false)->where('is_dismissed', false);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }
}
