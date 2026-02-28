<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AIRecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $recommendationId
    ) {}

    public function handle(AIRecommendationService $service): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        $service->generateRecommendations($user, $this->recommendationId);
    }
}
