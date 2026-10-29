<?php

namespace AhRaihan\SteadfastCourier\Enums;

class ReturnStatus
{
    public const PARTIAL_DELIVERED_RETURN_PROCESSING = 'partial_delivered_return_proccessing';

    public const PARTIAL_DELIVERED_RETURN_RIDER_ASSIGNED = 'partial_delivered_return_rider_assigned';

    public const PARTIAL_DELIVERED_RETURN_RECEIVED = 'partial_delivered_return_received';

    public const CANCELLED_RETURN_PROCESSING = 'cancelled_return_proccessing';

    public const CANCELLED_RETURN_RIDER_ASSIGNED = 'cancelled_return_rider_assigned';

    public const CANCELLED_RETURN_RECEIVED = 'cancelled_return_received';

    /**
     * Get all return statuses.
     */
    public static function all(): array
    {
        return [
            self::PARTIAL_DELIVERED_RETURN_PROCESSING,
            self::PARTIAL_DELIVERED_RETURN_RIDER_ASSIGNED,
            self::PARTIAL_DELIVERED_RETURN_RECEIVED,
            self::CANCELLED_RETURN_PROCESSING,
            self::CANCELLED_RETURN_RIDER_ASSIGNED,
            self::CANCELLED_RETURN_RECEIVED,
        ];
    }

    /**
     * Determine if a return is completed (merchant has parcel back).
     */
    public static function isReceived(string $status): bool
    {
        return in_array($status, [
            self::PARTIAL_DELIVERED_RETURN_RECEIVED,
            self::CANCELLED_RETURN_RECEIVED,
        ], true);
    }

    /**
     * Human readable English label.
     */
    public static function label(string $status): string
    {
        return match ($status) {
            self::PARTIAL_DELIVERED_RETURN_PROCESSING => 'Part Delivered (Return Processing)',
            self::PARTIAL_DELIVERED_RETURN_RIDER_ASSIGNED => 'Part Delivered (Return Rider Assigned)',
            self::PARTIAL_DELIVERED_RETURN_RECEIVED => 'Part Delivered (Return Received)',
            self::CANCELLED_RETURN_PROCESSING => 'Cancelled (Return Processing)',
            self::CANCELLED_RETURN_RIDER_ASSIGNED => 'Cancelled (Return Rider Assigned)',
            self::CANCELLED_RETURN_RECEIVED => 'Cancelled (Return Received)',
            default => $status,
        };
    }
}
