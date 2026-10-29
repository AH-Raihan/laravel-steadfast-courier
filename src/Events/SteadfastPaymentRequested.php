<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastPaymentRequested
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public ?string $idempotencyKey = null
    ) {}
}

