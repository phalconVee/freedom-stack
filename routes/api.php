<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DebtController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinancialProfileController;
use App\Http\Controllers\Api\FreedomCalculatorController;
use App\Http\Controllers\Api\InvestmentAccountController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::post('/calculator/freedom-number', [FreedomCalculatorController::class, 'calculate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class);

    Route::get('/profile', [FinancialProfileController::class, 'show']);
    Route::post('/profile', [FinancialProfileController::class, 'store']);
    Route::put('/profile', [FinancialProfileController::class, 'update']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/expenses/bulk', [ExpenseController::class, 'bulkStore']);
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    Route::apiResource('debts', DebtController::class);
    Route::apiResource('investment-accounts', InvestmentAccountController::class);

    Route::post('/calculator/freedom-number/save', [FreedomCalculatorController::class, 'calculateAndSave']);
    Route::get('/calculator/history', [FreedomCalculatorController::class, 'history']);

    Route::post('/recommendations/generate', [RecommendationController::class, 'generate']);
    Route::get('/recommendations/latest', [RecommendationController::class, 'latest']);
    Route::get('/recommendations/{id}', [RecommendationController::class, 'show']);
    Route::get('/recommendations/{id}/status', [RecommendationController::class, 'status']);

    Route::get('/progress', [ProgressController::class, 'index']);
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::get('/progress/summary', [ProgressController::class, 'summary']);
    Route::get('/progress/latest', [ProgressController::class, 'latest']);

    Route::post('/subscription/checkout', [\App\Http\Controllers\Api\SubscriptionController::class, 'checkout']);
    Route::get('/subscription/portal', [\App\Http\Controllers\Api\SubscriptionController::class, 'portal']);
});
