<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = InvestmentAccount::where('user_id', $request->user()->id)->get();

        return response()->json(['data' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:401k,403b,traditional_ira,roth_ira,hsa,brokerage,hysa,savings,other'],
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'integer', 'min:0'],
            'monthly_contribution' => ['nullable', 'integer', 'min:0'],
            'employer_match_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employer_match_limit' => ['nullable', 'integer', 'min:0'],
            'estimated_annual_return' => ['nullable', 'numeric', 'min:0', 'max:30'],
        ]);

        $account = InvestmentAccount::create([
            'user_id' => $request->user()->id,
            ...$validated,
            'monthly_contribution' => $validated['monthly_contribution'] ?? 0,
            'estimated_annual_return' => $validated['estimated_annual_return'] ?? 7.0,
        ]);

        return response()->json(['data' => $account], 201);
    }

    public function show(Request $request, InvestmentAccount $investment_account): JsonResponse
    {
        if ($investment_account->user_id !== $request->user()->id) {
            abort(404);
        }

        return response()->json(['data' => $investment_account]);
    }

    public function update(Request $request, InvestmentAccount $investment_account): JsonResponse
    {
        if ($investment_account->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:401k,403b,traditional_ira,roth_ira,hsa,brokerage,hysa,savings,other'],
            'name' => ['sometimes', 'string', 'max:255'],
            'balance' => ['sometimes', 'integer', 'min:0'],
            'monthly_contribution' => ['nullable', 'integer', 'min:0'],
            'employer_match_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employer_match_limit' => ['nullable', 'integer', 'min:0'],
            'estimated_annual_return' => ['nullable', 'numeric', 'min:0', 'max:30'],
        ]);

        $investment_account->update($validated);

        return response()->json(['data' => $investment_account]);
    }

    public function destroy(Request $request, InvestmentAccount $investment_account): JsonResponse
    {
        if ($investment_account->user_id !== $request->user()->id) {
            abort(404);
        }

        $investment_account->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
