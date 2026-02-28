<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateRecommendations;
use App\Models\AIRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    private const MAX_AI_CALLS_PER_DAY = 10;

    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = AIRecommendation::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($count >= self::MAX_AI_CALLS_PER_DAY) {
            return response()->json([
                'message' => 'Daily AI recommendation limit reached. Try again tomorrow.',
            ], 429);
        }

        $record = AIRecommendation::create([
            'user_id' => $user->id,
            'financial_profile_snapshot' => [],
            'model_used' => env('OPENROUTER_DEFAULT_MODEL', ''),
            'prompt_hash' => '',
            'status' => 'pending',
        ]);

        dispatch(new GenerateRecommendations($user->id, $record->id));

        return response()->json([
            'id' => $record->id,
            'status' => 'pending',
            'message' => 'Recommendations are being generated. Poll /recommendations/' . $record->id . '/status',
        ], 202);
    }

    public function latest(Request $request): JsonResponse
    {
        $rec = AIRecommendation::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->first();

        if (! $rec) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $rec->id,
                'status' => $rec->status,
                'recommendations' => $rec->recommendations,
                'debt_strategy' => $rec->debt_strategy,
                'key_insight' => $rec->key_insight,
                'created_at' => $rec->created_at->toIso8601String(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $rec = AIRecommendation::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $rec->id,
                'status' => $rec->status,
                'recommendations' => $rec->recommendations,
                'debt_strategy' => $rec->debt_strategy,
                'key_insight' => $rec->key_insight,
                'error_message' => $rec->error_message,
                'created_at' => $rec->created_at->toIso8601String(),
            ],
        ]);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $rec = AIRecommendation::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'id' => $rec->id,
            'status' => $rec->status,
            'completed' => $rec->status === 'completed',
            'failed' => $rec->status === 'failed',
        ]);
    }
}
