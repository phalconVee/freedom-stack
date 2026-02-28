<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIRecommendation extends Model
{
    protected $table = 'ai_recommendations';

    protected $fillable = [
        'user_id',
        'financial_profile_snapshot',
        'model_used',
        'prompt_hash',
        'recommendations',
        'debt_strategy',
        'key_insight',
        'input_tokens',
        'output_tokens',
        'cost_usd',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'financial_profile_snapshot' => 'array',
            'recommendations' => 'array',
            'debt_strategy' => 'array',
            'key_insight' => 'array',
            'cost_usd' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
