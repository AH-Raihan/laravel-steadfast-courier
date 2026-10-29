<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastTrackingUpdated
{
    use Dispatchable, SerializesModels;

    public int|string $consignmentId;

    public ?string $trackingCode;

    public ?string $trackingMessage;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public ?string $idempotencyKey = null
    ) {
        $this->consignmentId = $payload['consignment_id'] ?? 0;
        $this->trackingCode = $payload['tracking_code'] ?? null;
        $this->trackingMessage = $payload['tracking_message'] ?? ($payload['message'] ?? null);
    }
}
