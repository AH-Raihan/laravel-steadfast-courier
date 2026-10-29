<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastWebhookReceived
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $notificationType,
        public array $payload,
        public ?string $idempotencyKey = null,
        public ?string $signature = null
    ) {}
}

