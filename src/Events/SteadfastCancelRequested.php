<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastCancelRequested
{
    use Dispatchable, SerializesModels;

    public int|string $consignmentId;

    public ?string $invoice;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public ?string $idempotencyKey = null
    ) {
        $this->consignmentId = $payload['consignment_id'] ?? 0;
        $this->invoice = isset($payload['invoice']) ? (string) $payload['invoice'] : null;
    }
}
