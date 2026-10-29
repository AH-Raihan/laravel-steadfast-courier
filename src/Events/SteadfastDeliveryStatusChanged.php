<?php

namespace AhRaihan\SteadfastCourier\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SteadfastDeliveryStatusChanged
{
    use Dispatchable, SerializesModels;

    public int|string $consignmentId;

    public string $invoice;

    public float $codAmount;

    public string $status;

    public float $deliveryCharge;

    public ?string $trackingMessage;

    public ?string $updatedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $payload,
        public ?string $idempotencyKey = null
    ) {
        $this->consignmentId = $payload['consignment_id'] ?? 0;
        $this->invoice = (string) ($payload['invoice'] ?? '');
        $this->codAmount = (float) ($payload['cod_amount'] ?? 0.0);
        $this->status = (string) ($payload['status'] ?? 'unknown');
        $this->deliveryCharge = (float) ($payload['delivery_charge'] ?? 0.0);
        $this->trackingMessage = $payload['tracking_message'] ?? null;
        $this->updatedAt = $payload['updated_at'] ?? null;
    }

    /**
     * Check if package is successfully delivered.
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'partial_delivered'], true);
    }

    /**
     * Check if package delivery is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
