<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AIRecommendation;
use App\Models\Debt;
use App\Models\Expense;
use App\Models\FinancialProfile;
use App\Models\FreedomCalculation;
use App\Models\InvestmentAccount;
use App\Models\ProgressSnapshot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Single dashboard payload: welcome, profile completeness, stats, check-in, expenses, debts, recommendations, journey, badges.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $profile = FinancialProfile::where('user_id', $userId)->first();
        $expensesCount = Expense::where('user_id', $userId)->count();
        $freedomCalcs = FreedomCalculation::where('user_id', $userId)->orderByDesc('created_at')->get();
        $latestFreedom = $freedomCalcs->first();
        $debts = Debt::where('user_id', $userId)->get();
        $investments = InvestmentAccount::where('user_id', $userId)->get();
        $snapshots = ProgressSnapshot::where('user_id', $userId)->orderBy('snapshot_date')->get();
        $latestSnapshot = $snapshots->last();
        $previousSnapshot = $snapshots->count() >= 2 ? $snapshots->get($snapshots->count() - 2) : null;
        $latestRec = AIRecommendation::where('user_id', $userId)->where('status', 'completed')->orderByDesc('created_at')->first();
        $pendingRec = AIRecommendation::where('user_id', $userId)->whereIn('status', ['pending', 'processing'])->orderByDesc('created_at')->first();

        $expensesSumByCategory = Expense::where('user_id', $userId)
            ->select('category', DB::raw('SUM(monthly_amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = (int) Expense::where('user_id', $userId)->sum('monthly_amount');
        $totalInvested = (int) $investments->sum('balance');
        $totalDebt = (int) $debts->sum('balance');
        $netWorth = $totalInvested - $totalDebt;
        $freedomNumber = $latestFreedom?->freedom_number ?? 0;
        $freedomPct = $freedomNumber > 0 ? round($netWorth / $freedomNumber * 100, 1) : null;

        $hasIncome = $profile && (int) $profile->monthly_gross_income > 0;
        $hasDebtsOrFlag = $debts->isNotEmpty() || ($profile && $profile->has_no_debts);
        $hasInvestmentsOrFlag = $investments->isNotEmpty() || ($profile && $profile->has_no_investments);

        $profileCompleteness = $this->profileCompleteness(
            $expensesCount,
            $freedomCalcs->isNotEmpty(),
            $hasIncome,
            $hasDebtsOrFlag,
            $hasInvestmentsOrFlag
        );

        $welcome = $this->welcome($user, $profileCompleteness['complete']);

        $stats = $this->stats(
            $latestFreedom,
            $netWorth,
            $totalDebt,
            $totalInvested,
            $freedomNumber,
            $freedomPct,
            $hasDebtsOrFlag,
            $hasInvestmentsOrFlag,
            $hasIncome,
            $profile,
            $investments,
            $previousSnapshot,
            $latestSnapshot
        );

        $checkin = $this->checkinState($profileCompleteness['complete'], $latestSnapshot);

        $expensesSummary = [
            'total_cents' => $totalExpenses,
            'by_category' => $expensesSumByCategory->map(fn ($r) => [
                'category' => $r->category,
                'amount_cents' => (int) $r->total,
            ])->values()->all(),
        ];

        $debtSnapshot = $debts->map(fn (Debt $d) => [
            'id' => $d->id,
            'name' => $d->name,
            'balance_cents' => (int) $d->balance,
            'original_balance_cents' => (int) ($d->original_balance ?? $d->balance),
            'apr' => (float) $d->interest_rate,
            'payoff_pct' => $d->original_balance > 0
                ? (int) round($d->balance / $d->original_balance * 100)
                : 100,
        ])->values()->all();

        $recommendationsCard = $this->recommendationsCard($profileCompleteness['complete'], $latestRec, $pendingRec);

        $journey = $snapshots->map(fn (ProgressSnapshot $s) => [
            'snapshot_date' => $s->snapshot_date->format('Y-m-d'),
            'net_worth_cents' => (int) $s->net_worth,
        ])->values()->all();

        $badges = [
            'action_plan_count' => $latestRec && is_array($latestRec->recommendations)
                ? count($latestRec->recommendations)
                : 0,
            'progress_needs_update' => $checkin['state'] === 'nudge',
            'profile_incomplete' => ! $profileCompleteness['complete'],
        ];

        return response()->json([
            'data' => [
                'welcome' => $welcome,
                'profile_completeness' => $profileCompleteness,
                'stats' => $stats,
                'checkin' => $checkin,
                'expenses_summary' => $expensesSummary,
                'debts' => $debtSnapshot,
                'has_no_debts_flag' => $profile?->has_no_debts ?? false,
                'recommendations_card' => $recommendationsCard,
                'journey' => $journey,
                'badges' => $badges,
            ],
        ]);
    }

    private function profileCompleteness(
        int $expensesCount,
        bool $hasFreedomNumber,
        bool $hasIncome,
        bool $hasDebtsOrFlag,
        bool $hasInvestmentsOrFlag
    ): array {
        $steps = [
            [
                'id' => 1,
                'title' => 'Expenses',
                'sub' => 'Monthly expenses entered',
                'done' => $expensesCount > 0 || $hasFreedomNumber,
                'route' => null,
                'time' => null,
            ],
            [
                'id' => 2,
                'title' => 'Freedom Number',
                'sub' => 'Calculated',
                'done' => $hasFreedomNumber,
                'route' => null,
                'time' => null,
            ],
            [
                'id' => 3,
                'title' => 'Income details',
                'sub' => 'Needed for timeline calculation',
                'done' => $hasIncome,
                'route' => '/finances/profile',
                'time' => '~1 min',
            ],
            [
                'id' => 4,
                'title' => 'Add your debts',
                'sub' => 'Needed for payoff strategy',
                'done' => $hasDebtsOrFlag,
                'route' => '/finances/debts',
                'time' => '~2 min',
            ],
            [
                'id' => 5,
                'title' => 'Add savings & investments',
                'sub' => 'Needed for net worth & optimization',
                'done' => $hasInvestmentsOrFlag,
                'route' => '/finances/investments',
                'time' => '~2 min',
            ],
        ];

        $completed = collect($steps)->where('done', true)->count();
        $pct = (int) round($completed / 5 * 100);

        return [
            'steps' => $steps,
            'pct' => $pct,
            'complete' => $pct === 100,
        ];
    }

    private function welcome($user, bool $profileComplete): array
    {
        $first_name = $user->first_name ?: 'there';
        $lastLogin = $user->last_login_at ?? null;
        $createdAt = $user->created_at;
        $isNewOrFirstTime = ! $lastLogin || $createdAt->diffInHours(now()) < 24;

        if ($isNewOrFirstTime) {
            return [
                'title' => "Welcome to FreedomStack, {$first_name}",
                'subtitle' => 'Your Freedom Number is set. Complete your profile to unlock personalized recommendations.',
            ];
        }

        $month = now()->format('F');
        $year = now()->format('Y');

        return [
            'title' => "Welcome back, {$first_name}",
            'subtitle' => "Here's where you stand as of {$month} {$year}.",
        ];
    }

    private function stats(
        ?FreedomCalculation $latestFreedom,
        int $netWorth,
        int $totalDebt,
        int $totalInvested,
        int $freedomNumber,
        ?float $freedomPct,
        bool $hasDebtsOrFlag,
        bool $hasInvestmentsOrFlag,
        bool $hasIncome,
        ?FinancialProfile $profile,
        $investments,
        ?ProgressSnapshot $previousSnapshot,
        ?ProgressSnapshot $latestSnapshot
    ): array {
        $freedomNumberCents = $latestFreedom?->freedom_number ?? 0;
        $yearsToFreedom = $latestFreedom?->years_to_freedom ?? null;

        $canComputeNetWorth = $hasDebtsOrFlag || $hasInvestmentsOrFlag;
        $canComputeProgress = $canComputeNetWorth && $freedomNumber > 0;
        $canComputeTimeToFreedom = $hasIncome && $investments->isNotEmpty() && $freedomNumber > 0 && $yearsToFreedom !== null;

        $netWorthDelta = null;
        if ($canComputeNetWorth && $previousSnapshot !== null && $latestSnapshot !== null) {
            $diff = $latestSnapshot->net_worth - $previousSnapshot->net_worth;
            $netWorthDelta = ['value' => $diff, 'label' => 'vs previous month'];
        } elseif ($canComputeNetWorth && $latestSnapshot === null) {
            $netWorthDelta = ['value' => null, 'label' => 'First snapshot — track monthly to see changes'];
        }

        $progressDelta = null;
        if ($canComputeProgress && $previousSnapshot !== null && $latestSnapshot !== null) {
            $curr = (float) $latestSnapshot->freedom_pct_achieved;
            $prev = (float) $previousSnapshot->freedom_pct_achieved;
            $diff = round($curr - $prev, 1);
            $progressDelta = ['value' => $diff, 'label' => 'vs previous month'];
        } elseif ($canComputeProgress && $latestSnapshot === null) {
            $progressDelta = ['value' => null, 'label' => 'Log first check-in to track changes'];
        } elseif (! $canComputeProgress && ! $canComputeNetWorth) {
            $progressDelta = ['value' => null, 'label' => 'Needs net worth data'];
        }

        $ttfDelta = null;
        if ($canComputeTimeToFreedom && $previousSnapshot !== null && $latestSnapshot !== null && $previousSnapshot->estimated_months_to_freedom !== null) {
            $curr = (int) $latestSnapshot->estimated_months_to_freedom;
            $prev = (int) $previousSnapshot->estimated_months_to_freedom;
            $diffMonths = $prev - $curr;
            $ttfDelta = ['value' => $diffMonths, 'label' => 'vs previous month'];
        } elseif ($canComputeTimeToFreedom && $latestSnapshot === null) {
            $ttfDelta = ['value' => null, 'label' => 'With all recommended levers enabled'];
        }

        return [
            'freedom_number' => [
                'value_cents' => $freedomNumberCents,
                'delta' => null,
                'needs_data' => false,
                'cta_route' => null,
                'subtitle' => 'at 4% safe withdrawal rate',
            ],
            'net_worth' => [
                'value_cents' => $canComputeNetWorth ? $netWorth : null,
                'delta' => $netWorthDelta,
                'needs_data' => ! $canComputeNetWorth,
                'cta_route' => ! $canComputeNetWorth ? '/finances/debts' : null,
            ],
            'freedom_progress' => [
                'value_pct' => $canComputeProgress ? $freedomPct : null,
                'delta' => $progressDelta,
                'needs_data' => ! $canComputeProgress,
                'cta_route' => null,
            ],
            'time_to_freedom' => [
                'years' => $yearsToFreedom !== null ? (int) floor($yearsToFreedom) : null,
                'months' => $yearsToFreedom !== null ? (int) round(($yearsToFreedom - floor($yearsToFreedom)) * 12) : null,
                'delta' => $ttfDelta,
                'needs_data' => ! $canComputeTimeToFreedom,
                'cta_route' => ! $canComputeTimeToFreedom ? '/finances/profile' : null,
            ],
        ];
    }

    private function checkinState(bool $profileComplete, ?ProgressSnapshot $latestSnapshot): array
    {
        if (! $profileComplete) {
            return [
                'state' => 'getting_started',
                'title' => "You're just getting started",
                'subtitle' => 'Complete your profile first. Monthly check-ins begin after your first full month.',
                'button_text' => 'Got it',
                'button_route' => null,
                'next_checkin_date' => null,
                'last_snapshot_date' => null,
            ];
        }

        if ($latestSnapshot === null) {
            return [
                'state' => 'first',
                'title' => 'Log your first check-in',
                'subtitle' => 'Record your starting point so we can track your progress. Takes 60 seconds.',
                'button_text' => 'Log now',
                'button_route' => '/progress',
                'next_checkin_date' => null,
                'last_snapshot_date' => null,
            ];
        }

        $lastDate = Carbon::parse($latestSnapshot->snapshot_date);
        $daysSince = (int) $lastDate->diffInDays(now());
        $needsUpdate = $daysSince >= 25;
        $nextCheckin = $lastDate->copy()->addDays(30);

        if ($needsUpdate) {
            return [
                'state' => 'nudge',
                'title' => 'Time for your ' . now()->format('F') . ' check-in',
                'subtitle' => 'Last updated ' . $lastDate->format('F j') . '. Takes about 60 seconds.',
                'button_text' => 'Update now',
                'button_route' => '/progress',
                'next_checkin_date' => null,
                'last_snapshot_date' => $lastDate->format('Y-m-d'),
            ];
        }

        return [
            'state' => 'ok',
            'title' => "You're up to date",
            'subtitle' => 'Next check-in: ' . $nextCheckin->format('F j, Y'),
            'button_text' => null,
            'button_route' => '/progress',
            'next_checkin_date' => $nextCheckin->format('Y-m-d'),
            'last_snapshot_date' => $lastDate->format('Y-m-d'),
        ];
    }

    private function recommendationsCard(bool $profileComplete, $latestRec, $pendingRec): array
    {
        if (! $profileComplete) {
            return [
                'state' => 'locked',
                'title' => 'Complete your profile to unlock',
                'subtitle' => 'We need your income, debts, and savings to generate personalized recommendations.',
                'items' => [],
            ];
        }

        if ($pendingRec) {
            return [
                'state' => 'loading',
                'title' => 'Generating your action plan...',
                'subtitle' => null,
                'items' => [],
            ];
        }

        if (! $latestRec || ! is_array($latestRec->recommendations)) {
            return [
                'state' => 'empty',
                'title' => null,
                'subtitle' => null,
                'items' => [],
            ];
        }

        $items = array_slice($latestRec->recommendations, 0, 3);
        $items = array_map(function ($r, $i) {
            $title = is_array($r) ? ($r['title'] ?? $r['recommendation'] ?? 'Recommendation') : (string) $r;
            $impact = is_array($r) ? ($r['timeline_impact'] ?? $r['impact'] ?? null) : null;

            return [
                'id' => $i + 1,
                'title' => $title,
                'impact' => $impact,
            ];
        }, $items, array_keys($items));

        return [
            'state' => 'populated',
            'title' => null,
            'subtitle' => null,
            'items' => array_values($items),
        ];
    }
}
