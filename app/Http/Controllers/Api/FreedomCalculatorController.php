<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FreedomCalculation;
use App\Services\FreedomNumberCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FreedomCalculatorController extends Controller
{
    public function __construct(
        private FreedomNumberCalculator $calculator
    ) {}

    /**
     * Public: calculate freedom number (no auth). Body: monthly_expenses (cents), optional monthly_savings, expected_return.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_expenses' => ['required', 'integer', 'min:0'],
            'monthly_savings' => ['nullable', 'integer', 'min:0'],
            'current_savings' => ['nullable', 'integer', 'min:0'],
            'expected_return' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        $monthlyExpenses = (int) $validated['monthly_expenses'];
        $range = $this->calculator->calculateRange($monthlyExpenses);

        $yearsToFreedom = null;
        $defaultReturn = (string) ($validated['expected_return'] ?? 7);
        $currentSavings = (int) ($validated['current_savings'] ?? 0);
        $monthlySavings = (int) ($validated['monthly_savings'] ?? 0);
        $targetAt4 = $range['4'];
        if ($monthlySavings > 0 || $currentSavings > 0) {
            $yearsToFreedom = $this->calculator->yearsToFreedom(
                $currentSavings,
                $monthlySavings,
                $defaultReturn,
                $targetAt4
            );
        }

        return response()->json([
            'monthly_expenses' => $monthlyExpenses,
            'freedom_number_at_3_pct' => $range['3'],
            'freedom_number_at_4_pct' => $range['4'],
            'freedom_number_at_5_pct' => $range['5'],
            'freedom_number_at_6_pct' => $range['6'],
            'years_to_freedom' => $yearsToFreedom,
            'assumptions' => [
                'expected_return_pct' => $defaultReturn,
                'current_savings' => $currentSavings,
                'monthly_savings' => $monthlySavings,
            ],
        ]);
    }

    /**
     * Authenticated: calculate and save to history.
     */
    public function calculateAndSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_expenses' => ['required', 'integer', 'min:0'],
            'withdrawal_rate' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'monthly_savings' => ['nullable', 'integer', 'min:0'],
            'current_savings' => ['nullable', 'integer', 'min:0'],
            'expected_return' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        $monthlyExpenses = (int) $validated['monthly_expenses'];
        $withdrawalRate = (string) ($validated['withdrawal_rate'] ?? '4');
        $expectedReturn = (string) ($validated['expected_return'] ?? '7');
        $monthlySavings = (int) ($validated['monthly_savings'] ?? 0);
        $currentSavings = (int) ($validated['current_savings'] ?? 0);

        $freedomNumber = $this->calculator->calculate($monthlyExpenses, $withdrawalRate);
        $yearsToFreedom = $this->calculator->yearsToFreedom(
            $currentSavings,
            $monthlySavings,
            $expectedReturn,
            $freedomNumber
        );

        $calc = FreedomCalculation::create([
            'user_id' => $request->user()->id,
            'monthly_expenses_used' => $monthlyExpenses,
            'withdrawal_rate' => $withdrawalRate,
            'expected_return_rate' => $expectedReturn,
            'inflation_rate' => 3.0,
            'freedom_number' => $freedomNumber,
            'years_to_freedom' => $yearsToFreedom,
            'monthly_savings_rate' => null,
            'assumptions' => [
                'current_savings' => $currentSavings,
                'monthly_savings' => $monthlySavings,
            ],
        ]);

        $range = $this->calculator->calculateRange($monthlyExpenses);

        return response()->json([
            'calculation_id' => $calc->id,
            'monthly_expenses' => $monthlyExpenses,
            'freedom_number_at_3_pct' => $range['3'],
            'freedom_number_at_4_pct' => $range['4'],
            'freedom_number_at_5_pct' => $range['5'],
            'freedom_number_at_6_pct' => $range['6'],
            'years_to_freedom' => $yearsToFreedom,
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $items = FreedomCalculation::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (FreedomCalculation $c) => [
                'id' => $c->id,
                'monthly_expenses' => $c->monthly_expenses_used,
                'freedom_number' => $c->freedom_number,
                'withdrawal_rate' => $c->withdrawal_rate,
                'years_to_freedom' => $c->years_to_freedom,
                'created_at' => $c->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $items]);
    }
}
