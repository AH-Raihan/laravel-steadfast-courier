<?php

namespace AhRaihan\SteadfastCourier\Http\Controllers;

use AhRaihan\SteadfastCourier\Enums\WebhookEvent;
use AhRaihan\SteadfastCourier\Events\SteadfastCancelRequested;
use AhRaihan\SteadfastCourier\Events\SteadfastConsignmentUpdated;
use AhRaihan\SteadfastCourier\Events\SteadfastDeliveryStatusChanged;
use AhRaihan\SteadfastCourier\Events\SteadfastPaymentRequested;
use AhRaihan\SteadfastCourier\Events\SteadfastPickupRequested;
use AhRaihan\SteadfastCourier\Events\SteadfastReturnListAccepted;
use AhRaihan\SteadfastCourier\Events\SteadfastReturnRequested;
use AhRaihan\SteadfastCourier\Events\SteadfastTrackingUpdated;
use AhRaihan\SteadfastCourier\Events\SteadfastUserUpdated;
use AhRaihan\SteadfastCourier\Events\SteadfastWebhookReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming Steadfast Courier webhook request.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $raw = $request->getContent();
            $payload = json_decode($raw, true) ?: [];
        }

        $idempotencyKey = $request->header('Idempotency-Key');
        $signature = $request->header('X-Signature');

        // Check idempotency to prevent duplicate handling on Steadfast retries
        if (! empty($idempotencyKey) && (bool) config('steadfast.webhook.handle_idempotency', true)) {
            $cacheKey = 'steadfast_webhook_idempotency_'.md5($idempotencyKey);
            $cacheTtl = (int) config('steadfast.webhook.idempotency_cache_ttl', 86400);

            if (Cache::has($cacheKey)) {
                if ((bool) config('steadfast.webhook.log_events', true)) {
                    Log::info('Steadfast Webhook: Duplicate event ignored via Idempotency-Key', [
                        'idempotency_key' => $idempotencyKey,
                    ]);
                }

                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Duplicate request already processed.',
                ], 200);
            }

            Cache::put($cacheKey, true, $cacheTtl);
        }

        $notificationType = (string) ($payload['notification_type'] ?? 'unknown');

        if ((bool) config('steadfast.webhook.log_events', true)) {
            Log::info('Steadfast Webhook Event: '.$notificationType, [
                'notification_type' => $notificationType,
                'idempotency_key' => $idempotencyKey,
                'payload' => $payload,
            ]);
        }

        // Fire general event for any received webhook
        event(new SteadfastWebhookReceived($notificationType, $payload, $idempotencyKey, $signature));

        // Dispatch specific strongly-typed event
        $this->dispatchTypedEvent($notificationType, $payload, $idempotencyKey);

        return new JsonResponse([
            'status' => 'success',
            'notification_type' => $notificationType,
        ], 200);
    }

    /**
     * Dispatch typed event according to notification_type.
     */
    protected function dispatchTypedEvent(string $type, array $payload, ?string $idempotencyKey): void
    {
        match ($type) {
            WebhookEvent::DELIVERY_STATUS => event(new SteadfastDeliveryStatusChanged($payload, $idempotencyKey)),
            WebhookEvent::TRACKING_UPDATE => event(new SteadfastTrackingUpdated($payload, $idempotencyKey)),
            WebhookEvent::CONSIGNMENT_UPDATE => event(new SteadfastConsignmentUpdated($payload, $idempotencyKey)),
            WebhookEvent::RETURN_LIST_ACCEPTED => event(new SteadfastReturnListAccepted($payload, $idempotencyKey)),
            WebhookEvent::PAYMENT_REQUEST => event(new SteadfastPaymentRequested($payload, $idempotencyKey)),
            WebhookEvent::CANCEL_REQUEST => event(new SteadfastCancelRequested($payload, $idempotencyKey)),
            WebhookEvent::RETURN_REQUEST => event(new SteadfastReturnRequested($payload, $idempotencyKey)),
            WebhookEvent::PICKUP_REQUEST => event(new SteadfastPickupRequested($payload, $idempotencyKey)),
            WebhookEvent::USER_UPDATE => event(new SteadfastUserUpdated($payload, $idempotencyKey)),
            default => null,
        };
    }
}

