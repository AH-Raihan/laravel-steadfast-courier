<?php

namespace AhRaihan\SteadfastCourier\Enums;

class DeliveryStatus
{
    public const PENDING = 'pending';

    public const IN_REVIEW = 'in_review';

    public const HOLD = 'hold';

    public const DELIVERED_APPROVAL_PENDING = 'delivered_approval_pending';

    public const PARTIAL_DELIVERED_APPROVAL_PENDING = 'partial_delivered_approval_pending';

    public const CANCELLED_APPROVAL_PENDING = 'cancelled_approval_pending';

    public const UNKNOWN_APPROVAL_PENDING = 'unknown_approval_pending';

    public const DELIVERED = 'delivered';

    public const PARTIAL_DELIVERED = 'partial_delivered';

    public const CANCELLED = 'cancelled';

    public const EXCEPTIONAL = 'exceptional';

    public const UNKNOWN = 'unknown';

    /**
     * Get all delivery statuses.
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::IN_REVIEW,
            self::HOLD,
            self::DELIVERED_APPROVAL_PENDING,
            self::PARTIAL_DELIVERED_APPROVAL_PENDING,
            self::CANCELLED_APPROVAL_PENDING,
            self::UNKNOWN_APPROVAL_PENDING,
            self::DELIVERED,
            self::PARTIAL_DELIVERED,
            self::CANCELLED,
            self::EXCEPTIONAL,
            self::UNKNOWN,
        ];
    }

    /**
     * Determine if a status indicates completed delivery.
     */
    public static function isDelivered(string $status): bool
    {
        return in_array($status, [self::DELIVERED, self::PARTIAL_DELIVERED], true);
    }

    /**
     * Determine if a status is cancelled.
     */
    public static function isCancelled(string $status): bool
    {
        return $status === self::CANCELLED;
    }

    /**
     * Determine if a status is still awaiting confirmation from Steadfast accounts team.
     */
    public static function isPendingApproval(string $status): bool
    {
        return str_ends_with($status, '_approval_pending');
    }

    /**
     * Human readable English label.
     */
    public static function label(string $status): string
    {
        return match ($status) {
            self::PENDING => 'Pending',
            self::IN_REVIEW => 'In Review',
            self::HOLD => 'On Hold',
            self::DELIVERED_APPROVAL_PENDING => 'Delivered (Approval Pending)',
            self::PARTIAL_DELIVERED_APPROVAL_PENDING => 'Partial Delivered (Approval Pending)',
            self::CANCELLED_APPROVAL_PENDING => 'Cancelled (Approval Pending)',
            self::UNKNOWN_APPROVAL_PENDING => 'Unknown (Approval Pending)',
            self::DELIVERED => 'Delivered',
            self::PARTIAL_DELIVERED => 'Partial Delivered',
            self::CANCELLED => 'Cancelled',
            self::EXCEPTIONAL => 'Exceptional',
            default => 'Unknown',
        };
    }
}
