<?php

namespace AhRaihan\SteadfastCourier\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $verifySignature = (bool) config('steadfast.webhook.verify_signature', true);

        if (! $verifySignature) {
            return $next($request);
        }

        $token = (string) config('steadfast.webhook.auth_token', '');

        // If no token is configured yet, allow in non-production or log warning
        if (empty($token)) {
            Log::warning('Steadfast Webhook: No webhook auth_token configured in steadfast.php or .env (STEADFAST_WEBHOOK_TOKEN).');

            return $next($request);
        }

        $signature = $request->header('X-Signature');

        if (empty($signature)) {
            Log::warning('Steadfast Webhook: Missing X-Signature header.');

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing X-Signature header.',
            ], 401);
        }

        $rawBody = $request->getContent();
        $expected = hash_hmac('sha256', $rawBody, $token);

        if (! hash_equals($expected, (string) $signature)) {
            Log::warning('Steadfast Webhook: Signature mismatch.', [
                'expected' => $expected,
                'received' => $signature,
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid signature.',
            ], 401);
        }

        return $next($request);
    }
}

