<?php

namespace App\Services;

use App\Data\FinancialDataSanitizer;
use App\Models\AIRecommendation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIRecommendationService
{
    public function __construct(
        private FinancialDataSanitizer $sanitizer,
        private FreedomNumberCalculator $freedomCalculator,
        private DebtPayoffOptimizer $debtOptimizer,
        private TimelineProjectionService $timelineService
    ) {}

    /**
     * Run PHP calculations, sanitize data, call OpenRouter, store result.
     */
    public function generateRecommendations(User $user, int $recommendationId): AIRecommendation
    {
        $profile = $user->financialProfile;
        $sanitized = $this->sanitizer->sanitizeForAI($user);

        $freedomAt4 = $profile
            ? $this->freedomCalculator->calculate($profile->monthly_expenses_total, '4')
            : 0;
        $range = $profile
            ? $this->freedomCalculator->calculateRange($profile->monthly_expenses_total)
            : ['3' => 0, '4' => 0, '5' => 0, '6' => 0];

        $debtsForOptimizer = $user->debts->map(fn ($d) => [
            'balance' => $d->balance,
            'interest_rate' => (float) $d->interest_rate,
            'minimum_payment' => $d->minimum_payment,
            'name' => $d->name,
        ])->toArray();

        $debtComparison = $this->debtOptimizer->compareMethods($debtsForOptimizer, 0);
        $totalInvested = $user->investmentAccounts->sum('balance');
        $monthlyContrib = $user->investmentAccounts->sum('monthly_contribution');
        $monthsToFreedom = $this->timelineService->projectMonths(
            $totalInvested,
            $monthlyContrib,
            '7',
            $freedomAt4
        );

        $promptHash = hash('sha256', json_encode($sanitized) . $freedomAt4);

        $record = AIRecommendation::findOrFail($recommendationId);
        $record->update([
            'financial_profile_snapshot' => $sanitized,
            'model_used' => env('OPENROUTER_DEFAULT_MODEL', 'deepseek/deepseek-v3.2'),
            'prompt_hash' => $promptHash,
            'status' => 'processing',
        ]);

        try {
            $systemPrompt = $this->buildSystemPrompt();
            $userPrompt = $this->buildUserPrompt($sanitized, $range, $debtComparison, $monthsToFreedom);

            $baseUrl = config('openai.base_uri', 'https://openrouter.ai/api/v1');
            $apiKey = config('openai.api_key');
            $model = env('OPENROUTER_DEFAULT_MODEL', 'deepseek/deepseek-v3.2');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
            ])->timeout(60)->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 2000,
            ]);

            if (! $response->successful()) {
                $record->update([
                    'status' => 'failed',
                    'error_message' => $response->body(),
                ]);
                return $record;
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? '';
            $usage = $body['usage'] ?? [];
            $inputTokens = $usage['prompt_tokens'] ?? null;
            $outputTokens = $usage['completion_tokens'] ?? null;

            $parsed = $this->parseResponse($content);

            $record->update([
                'recommendations' => $parsed['recommendations'] ?? null,
                'debt_strategy' => $parsed['debt_strategy'] ?? null,
                'key_insight' => $parsed['key_insight'] ?? null,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => null,
                'status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            Log::error('AI recommendation failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $record->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $record;
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a certified financial planning assistant. You receive a user's anonymized financial profile and pre-calculated results.

RULES:
- Do NOT perform any calculations. All numbers are pre-calculated. Reference them only.
- Respond ONLY with valid JSON. No markdown code fences.
- Use this exact schema:
{"summary":"2-3 sentence overview","recommendations":[{"id":"1","title":"string","description":"string","category":"debt_payoff|savings|retirement|income|expense_reduction","priority":1,"timeline_impact_months":0,"confidence":"high|medium|low","specific_actions":["step1","step2"]}],"debt_strategy":{"method":"avalanche|snowball","order":["Debt 1"],"reasoning":"string"},"key_insight":"one powerful takeaway"}
- Maximum 5 recommendations, ranked by impact.
- Default to avalanche for debt. Prioritize by timeline reduction.
PROMPT;
    }

    private function buildUserPrompt(array $sanitized, array $range, array $debtComparison, ?int $monthsToFreedom): string
    {
        $lines = [
            'FINANCIAL PROFILE:',
            '- Monthly net income (cents): ' . $sanitized['monthly_net_income'],
            '- Monthly expenses total (cents): ' . $sanitized['monthly_expenses_total'],
            '- Filing status: ' . $sanitized['filing_status'],
            '- Risk tolerance: ' . $sanitized['risk_tolerance'],
            '',
            'PRE-CALCULATED FREEDOM NUMBERS (cents):',
            '- 3% SWR: ' . $range['3'],
            '- 4% SWR: ' . $range['4'],
            '- 5% SWR: ' . $range['5'],
            '- 6% SWR: ' . $range['6'],
            '- Estimated months to freedom at current savings: ' . ($monthsToFreedom ?? 'N/A'),
            '',
            'DEBT (avalanche): months=' . ($debtComparison['avalanche']['total_months'] ?? 0) . ', total_interest=' . ($debtComparison['avalanche']['total_interest'] ?? 0) . ', debt_free_date=' . ($debtComparison['avalanche']['debt_free_date'] ?? ''),
            'DEBT (snowball): months=' . ($debtComparison['snowball']['total_months'] ?? 0) . ', interest_saved (avalanche vs snowball): ' . ($debtComparison['interest_saved'] ?? 0),
            '',
            'Generate a short, actionable JSON plan.',
        ];

        return implode("\n", $lines);
    }

    private function parseResponse(string $content): array
    {
        $content = trim(preg_replace('/^```\w*\n?|\n?```$/', '', $content));
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : ['recommendations' => [], 'key_insight' => 'Unable to parse response.'];
    }
}
