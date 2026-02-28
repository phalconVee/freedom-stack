<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreedomCalculation extends Model
{
    protected $fillable = [
        'user_id',
        'monthly_expenses_used',
        'withdrawal_rate',
        'expected_return_rate',
        'inflation_rate',
        'freedom_number',
        'years_to_freedom',
        'monthly_savings_rate',
        'assumptions',
    ];

    protected function casts(): array
    {
        return [
            'monthly_expenses_used' => 'integer',
            'freedom_number' => 'integer',
            'withdrawal_rate' => 'decimal:2',
            'expected_return_rate' => 'decimal:2',
            'inflation_rate' => 'decimal:2',
            'years_to_freedom' => 'decimal:2',
            'monthly_savings_rate' => 'decimal:2',
            'assumptions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
