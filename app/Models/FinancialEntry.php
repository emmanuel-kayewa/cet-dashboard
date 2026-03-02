<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'directorate_id',
        'category',
        'sub_category',
        'description',
        'amount',
        'budgeted_amount',
        'currency',
        'period_date',
        'period_type',
        'source',
        'notes',
        'entered_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'budgeted_amount' => 'decimal:2',
        'period_date' => 'date',
    ];

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function getBudgetVariance(): ?float
    {
        if (!$this->budgeted_amount || $this->budgeted_amount == 0) {
            return null;
        }
        return round((($this->amount - $this->budgeted_amount) / $this->budgeted_amount) * 100, 2);
    }

    public function getBudgetUtilization(): ?float
    {
        if (!$this->budgeted_amount || $this->budgeted_amount == 0) {
            return null;
        }
        return round(($this->amount / $this->budgeted_amount) * 100, 2);
    }
}
