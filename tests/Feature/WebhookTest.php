<?php

namespace AhRaihan\SteadfastCourier\Tests\Feature;

use AhRaihan\SteadfastCourier\Events\SteadfastDeliveryStatusChanged;
use AhRaihan\SteadfastCourier\Events\SteadfastWebhookReceived;
use AhRaihan\SteadfastCourier\Tests\TestCase;
use Illuminate\Support\Facades\Event;

require_once __DIR__.'/../TestCase.php';

class WebhookTest extends TestCase
{
    public function test_it_verifies_webhook_signature_and_dispatches_delivery_status_event(): void
    {
        Event::fake([
            SteadfastWebhookReceived::class,
            SteadfastDeliveryStatusChanged::class,
        ]);

        $payload = [
            'notification_type' => 'delivery_status',
            'consignment_id' => 12345,
            'invoice' => 'INV-67890',
            'cod_amount' => 1500.0,
            'status' => 'delivered',
            'delivery_charge' => 100.0,
            'tracking_message' => 'Your package has been delivered successfully.',
            'updated_at' => '2026-03-02 12:45:30',
        ];

        $rawBody = json_encode($payload);
        $token = 'test_webhook_token_789';
        $signature = hash_hmac('sha256', $rawBody, $token);

        $response = $this->call(
            'POST',
            'steadfast/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SIGNATURE' => $signature,
                'HTTP_IDEMPOTENCY_KEY' => 'idem_key_001',
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'notification_type' => 'delivery_status',
        ]);

        Event::assertDispatched(SteadfastWebhookReceived::class, function ($event) {
            return $event->notificationType === 'delivery_status'
                && $event->payload['consignment_id'] === 12345;
        });

        Event::assertDispatched(SteadfastDeliveryStatusChanged::class, function ($event) {
            return $event->consignmentId === 12345
                && $event->invoice === 'INV-67890'
                && $event->status === 'delivered'
                && $event->isDelivered() === true
                && $event->codAmount === 1500.0;
        });
    }

    public function test_it_rejects_webhook_with_invalid_signature(): void
    {
        $payload = ['notification_type' => 'delivery_status'];
        $rawBody = json_encode($payload);

        $response = $this->call(
            'POST',
            'steadfast/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_SIGNATURE' => 'invalid_signature_hex_value',
            ],
            $rawBody
        );

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid signature.',
        ]);
    }

    public function test_it_handles_idempotent_duplicate_events(): void
    {
        Event::fake();

        $payload = [
            'notification_type' => 'delivery_status',
            'consignment_id' => 99999,
        ];

        $rawBody = json_encode($payload);
        $token = 'test_webhook_token_789';
        $signature = hash_hmac('sha256', $rawBody, $token);

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_IDEMPOTENCY_KEY' => 'test_idem_duplicate_123',
        ];

        // First attempt - processes successfully
        $first = $this->call('POST', 'steadfast/webhook', [], [], [], $headers, $rawBody);
        $first->assertStatus(200);

        // Second retry attempt with same Idempotency-Key
        $second = $this->call('POST', 'steadfast/webhook', [], [], [], $headers, $rawBody);
        $second->assertStatus(200);
        $second->assertJson([
            'status' => 'success',
            'message' => 'Duplicate request already processed.',
        ]);
    }
}
