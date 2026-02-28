<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionGate
{
    /**
     * Allow if user has active subscription, trial, or lifetime access.
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($user->lifetime_access) {
            return $next($request);
        }

        if ($user->onTrial()) {
            return $next($request);
        }

        if ($user->subscribed($plans ? $plans[0] : 'default')) {
            return $next($request);
        }

        return response()->json([
            'message' => 'This feature requires an active subscription.',
            'requires_subscription' => true,
        ], 403);
    }
}
