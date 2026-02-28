<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $debts = Debt::where('user_id', $request->user()->id)->orderByDesc('interest_rate')->get();

        return response()->json(['data' => $debts]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:student_loan,credit_card,auto_loan,mortgage,personal_loan,medical,other'],
            'name' => ['required', 'string', 'max:255'],
            'balance' => ['required', 'integer', 'min:0'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'minimum_payment' => ['required', 'integer', 'min:0'],
            'original_balance' => ['nullable', 'integer', 'min:0'],
            'loan_term_months' => ['nullable', 'integer', 'min:1'],
            'is_federal_student_loan' => ['nullable', 'boolean'],
            'repayment_plan' => ['nullable', 'string', 'max:64'],
        ]);

        $debt = Debt::create([
            'user_id' => $request->user()->id,
            ...$validated,
            'is_federal_student_loan' => $validated['is_federal_student_loan'] ?? false,
        ]);

        return response()->json(['data' => $debt], 201);
    }

    public function show(Request $request, Debt $debt): JsonResponse
    {
        if ($debt->user_id !== $request->user()->id) {
            abort(404);
        }

        return response()->json(['data' => $debt]);
    }

    public function update(Request $request, Debt $debt): JsonResponse
    {
        if ($debt->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:student_loan,credit_card,auto_loan,mortgage,personal_loan,medical,other'],
            'name' => ['sometimes', 'string', 'max:255'],
            'balance' => ['sometimes', 'integer', 'min:0'],
            'interest_rate' => ['sometimes', 'numeric', 'min:0', 'max:50'],
            'minimum_payment' => ['sometimes', 'integer', 'min:0'],
            'original_balance' => ['nullable', 'integer', 'min:0'],
            'loan_term_months' => ['nullable', 'integer', 'min:1'],
            'is_federal_student_loan' => ['nullable', 'boolean'],
            'repayment_plan' => ['nullable', 'string', 'max:64'],
        ]);

        $debt->update($validated);

        return response()->json(['data' => $debt]);
    }

    public function destroy(Request $request, Debt $debt): JsonResponse
    {
        if ($debt->user_id !== $request->user()->id) {
            abort(404);
        }

        $debt->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
