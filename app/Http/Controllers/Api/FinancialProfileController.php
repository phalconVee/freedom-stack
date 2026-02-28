<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = FinancialProfile::where('user_id', $request->user()->id)->first();

        return response()->json(['data' => $profile]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_gross_income' => ['required', 'integer', 'min:0'],
            'monthly_net_income' => ['required', 'integer', 'min:0'],
            'monthly_expenses_total' => ['required', 'integer', 'min:0'],
            'filing_status' => ['required', 'string', 'in:single,married_joint,married_separate,head_of_household'],
            'state_of_residence' => ['nullable', 'string', 'size:2'],
            'target_fire_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'current_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'risk_tolerance' => ['nullable', 'string', 'in:conservative,moderate,aggressive'],
        ]);

        $profile = FinancialProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['risk_tolerance' => $validated['risk_tolerance'] ?? 'moderate'])
        );

        return response()->json(['data' => $profile], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_gross_income' => ['sometimes', 'integer', 'min:0'],
            'monthly_net_income' => ['sometimes', 'integer', 'min:0'],
            'monthly_expenses_total' => ['sometimes', 'integer', 'min:0'],
            'filing_status' => ['sometimes', 'string', 'in:single,married_joint,married_separate,head_of_household'],
            'state_of_residence' => ['nullable', 'string', 'size:2'],
            'target_fire_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'current_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'risk_tolerance' => ['sometimes', 'string', 'in:conservative,moderate,aggressive'],
        ]);

        $profile = FinancialProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['risk_tolerance' => $validated['risk_tolerance'] ?? 'moderate'])
        );

        return response()->json(['data' => $profile]);
    }
}
