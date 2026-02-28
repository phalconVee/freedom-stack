<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\FreedomCalculation;
use App\Models\InvestmentAccount;
use App\Models\ProgressSnapshot;
use App\Services\FreedomNumberCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(
        private FreedomNumberCalculator $freedomCalculator
    ) {}

    public function index(Request $request): JsonResponse
    {
        $snapshots = ProgressSnapshot::where('user_id', $request->user()->id)
            ->orderByDesc('snapshot_date')
            ->limit(24)
            ->get();

        return response()->json(['data' => $snapshots]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'snapshot_date' => ['required', 'date'],
            'net_worth' => ['required', 'integer'],
            'total_debt' => ['required', 'integer', 'min:0'],
            'total_invested' => ['required', 'integer', 'min:0'],
            'total_savings' => ['required', 'integer', 'min:0'],
            'emergency_fund' => ['required', 'integer', 'min:0'],
            'monthly_income' => ['required', 'integer', 'min:0'],
            'monthly_expenses' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $income = (int) $validated['monthly_income'];
        $expenses = (int) $validated['monthly_expenses'];
        $savingsRate = $income > 0 ? round(($income - $expenses) / $income * 100, 2) : 0.0;

        $freedomNumber = $this->freedomCalculator->calculate($expenses, '4');
        $freedomPct = $freedomNumber > 0
            ? round(($validated['total_invested'] + $validated['total_savings']) / $freedomNumber * 100, 2)
            : 0.0;

        $snapshot = ProgressSnapshot::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'snapshot_date' => $validated['snapshot_date'],
            ],
            array_merge($validated, [
                'savings_rate_pct' => $savingsRate,
                'freedom_number' => $freedomNumber,
                'freedom_pct_achieved' => $freedomPct,
                'estimated_months_to_freedom' => null,
            ])
        );

        return response()->json(['data' => $snapshot], 201);
    }

    /**
     * Summary for progress/gap-analysis. When user has no snapshots yet (e.g. just finished onboarding),
     * return a computed summary from current debts, investments, and latest freedom calculation.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $latest = ProgressSnapshot::where('user_id', $userId)
            ->orderByDesc('snapshot_date')
            ->first();

        if ($latest) {
            return response()->json([
                'data' => [
                    'net_worth' => $latest->net_worth,
                    'total_debt' => $latest->total_debt,
                    'total_invested' => $latest->total_invested,
                    'freedom_number' => $latest->freedom_number,
                    'freedom_pct_achieved' => $latest->freedom_pct_achieved,
                    'estimated_months_to_freedom' => $latest->estimated_months_to_freedom,
                    'snapshot_date' => $latest->snapshot_date->format('Y-m-d'),
                ],
            ]);
        }

        // No snapshot yet: compute from current debts + investments + freedom_calculations (for gap-analysis / first-time users)
        $totalDebt = (int) Debt::where('user_id', $userId)->sum('balance');
        $totalInvested = (int) InvestmentAccount::where('user_id', $userId)->sum('balance');
        $netWorth = $totalInvested - $totalDebt;
        $latestFreedom = FreedomCalculation::where('user_id', $userId)->orderByDesc('created_at')->first();
        $freedomNumber = $latestFreedom ? (int) $latestFreedom->freedom_number : null;
        $freedomPctAchieved = $freedomNumber && $freedomNumber > 0
            ? round($netWorth / $freedomNumber * 100, 1)
            : null;

        return response()->json([
            'data' => [
                'net_worth' => $netWorth,
                'total_debt' => $totalDebt,
                'total_invested' => $totalInvested,
                'freedom_number' => $freedomNumber,
                'freedom_pct_achieved' => $freedomPctAchieved,
                'estimated_months_to_freedom' => null,
                'snapshot_date' => null,
            ],
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $snapshot = ProgressSnapshot::where('user_id', $request->user()->id)
            ->orderByDesc('snapshot_date')
            ->first();

        return response()->json(['data' => $snapshot]);
    }
}
