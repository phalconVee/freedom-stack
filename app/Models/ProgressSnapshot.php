<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'snapshot_date',
        'net_worth',
        'total_debt',
        'total_invested',
        'total_savings',
        'emergency_fund',
        'monthly_income',
        'monthly_expenses',
        'savings_rate_pct',
        'freedom_number',
        'freedom_pct_achieved',
        'estimated_months_to_freedom',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'net_worth' => 'integer',
            'total_debt' => 'integer',
            'total_invested' => 'integer',
            'total_savings' => 'integer',
            'emergency_fund' => 'integer',
            'monthly_income' => 'integer',
            'monthly_expenses' => 'integer',
            'freedom_number' => 'integer',
            'savings_rate_pct' => 'decimal:2',
            'freedom_pct_achieved' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
