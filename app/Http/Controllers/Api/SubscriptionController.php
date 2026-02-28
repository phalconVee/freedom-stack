<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Create Stripe Checkout session for subscription. Requires Stripe products/prices configured.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $priceId = config('cashier.price_id_premium', env('STRIPE_PRICE_ID'));

        if (! $priceId) {
            return response()->json([
                'message' => 'Stripe not configured. Set STRIPE_PRICE_ID.',
            ], 503);
        }

        $checkout = $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => config('app.frontend_url', config('app.url')) . '/dashboard/progress?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url', config('app.url')) . '/profile/financial',
            ]);

        return response()->json([
            'checkout_url' => $checkout->redirect()->getTargetUrl(),
        ]);
    }

    /**
     * Portal link for managing subscription.
     */
    public function portal(Request $request): JsonResponse
    {
        $response = $request->user()->redirectToBillingPortal(
            config('app.frontend_url', config('app.url')) . '/profile/financial'
        );

        return response()->json(['portal_url' => $response->getTargetUrl()]);
    }
}
