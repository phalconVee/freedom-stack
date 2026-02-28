<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialProfile extends Model
{
    protected $fillable = [
        'user_id',
        'monthly_gross_income',
        'monthly_net_income',
        'monthly_expenses_total',
        'filing_status',
        'state_of_residence',
        'target_fire_age',
        'current_age',
        'risk_tolerance',
        'has_no_debts',
        'has_no_investments',
    ];

    protected $casts = [
        'monthly_gross_income' => 'integer',
        'monthly_net_income' => 'integer',
        'monthly_expenses_total' => 'integer',
        'has_no_debts' => 'boolean',
        'has_no_investments' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
