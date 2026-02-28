<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentAccount extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'balance',
        'monthly_contribution',
        'employer_match_pct',
        'employer_match_limit',
        'estimated_annual_return',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'monthly_contribution' => 'integer',
            'employer_match_limit' => 'integer',
            'employer_match_pct' => 'decimal:2',
            'estimated_annual_return' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
