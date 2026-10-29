<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastReturnRequested
{
    use Dispatchable, SerializesModels;

    public int|string $consignmentId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public ?string $idempotencyKey = null
    ) {
        $this->consignmentId = $payload['consignment_id'] ?? 0;
    }
}

