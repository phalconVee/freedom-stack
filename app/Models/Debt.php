<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'balance',
        'interest_rate',
        'minimum_payment',
        'original_balance',
        'loan_term_months',
        'is_federal_student_loan',
        'repayment_plan',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'minimum_payment' => 'integer',
            'original_balance' => 'integer',
            'interest_rate' => 'decimal:2',
            'is_federal_student_loan' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
