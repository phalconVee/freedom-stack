<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $expenses = Expense::where('user_id', $request->user()->id)->orderBy('category')->get();

        return response()->json(['data' => $expenses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:housing,transportation,food,insurance,healthcare,utilities,debt_payments,personal,entertainment,education,savings,other'],
            'name' => ['required', 'string', 'max:255'],
            'monthly_amount' => ['required', 'integer', 'min:0'],
            'is_essential' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense = Expense::create([
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'name' => $validated['name'],
            'monthly_amount' => (int) $validated['monthly_amount'],
            'is_essential' => $validated['is_essential'] ?? true,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['data' => $expense], 201);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expenses' => ['required', 'array'],
            'expenses.*.category' => ['required', 'string', 'in:housing,transportation,food,insurance,healthcare,utilities,debt_payments,personal,entertainment,education,savings,other'],
            'expenses.*.name' => ['required', 'string', 'max:255'],
            'expenses.*.monthly_amount' => ['required', 'integer', 'min:0'],
            'expenses.*.is_essential' => ['nullable', 'boolean'],
            'expenses.*.notes' => ['nullable', 'string'],
        ]);

        $userId = $request->user()->id;
        $created = [];
        foreach ($validated['expenses'] as $e) {
            $created[] = Expense::create([
                'user_id' => $userId,
                'category' => $e['category'],
                'name' => $e['name'],
                'monthly_amount' => (int) $e['monthly_amount'],
                'is_essential' => $e['is_essential'] ?? true,
                'notes' => $e['notes'] ?? null,
            ]);
        }

        return response()->json(['data' => $created], 201);
    }

    public function show(Request $request, Expense $expense): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(404);
        }

        return response()->json(['data' => $expense]);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'category' => ['sometimes', 'string', 'in:housing,transportation,food,insurance,healthcare,utilities,debt_payments,personal,entertainment,education,savings,other'],
            'name' => ['sometimes', 'string', 'max:255'],
            'monthly_amount' => ['sometimes', 'integer', 'min:0'],
            'is_essential' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense->update($validated);

        return response()->json(['data' => $expense]);
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(404);
        }

        $expense->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
