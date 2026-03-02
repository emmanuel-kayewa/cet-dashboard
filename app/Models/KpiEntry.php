<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'kpi_id',
        'directorate_id',
        'value',
        'previous_value',
        'period_date',
        'period_type',
        'source',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'period_date' => 'date',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(Kpi::class);
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function scopeForPeriod($query, string $type, $date)
    {
        return $query->where('period_type', $type)->where('period_date', $date);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function getChangePercentage(): ?float
    {
        if (!$this->previous_value || $this->previous_value == 0) {
            return null;
        }
        return round((($this->value - $this->previous_value) / $this->previous_value) * 100, 2);
    }
}
