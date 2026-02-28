<?php

use Illuminate\Support\Facades\Route;

Route::post('stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::get('{any?}', function () {
    $response = response()->view('application');
    // Prevent caching the HTML shell in development so dashboard/JS updates are picked up
    if (config('app.debug')) {
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
    }
    return $response;
})->where('any', '.*');