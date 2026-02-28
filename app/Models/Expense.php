<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'name',
        'monthly_amount',
        'is_essential',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'integer',
            'is_essential' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
