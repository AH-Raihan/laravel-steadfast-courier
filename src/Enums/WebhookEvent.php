<?php

namespace AhRaihan\SteadfastCourier\Enums;

class WebhookEvent
{
    public const DELIVERY_STATUS = 'delivery_status';

    public const TRACKING_UPDATE = 'tracking_update';

    public const CONSIGNMENT_UPDATE = 'consignment_update';

    public const RETURN_LIST_ACCEPTED = 'return_list_accepted';

    public const PAYMENT_REQUEST = 'payment_request';

    public const CANCEL_REQUEST = 'cancel_request';

    public const RETURN_REQUEST = 'return_request';

    public const PICKUP_REQUEST = 'pickup_request';

    public const USER_UPDATE = 'user_update';

    /**
     * Get all supported webhook notification events.
     */
    public static function all(): array
    {
        return [
            self::DELIVERY_STATUS,
            self::TRACKING_UPDATE,
            self::CONSIGNMENT_UPDATE,
            self::RETURN_LIST_ACCEPTED,
            self::PAYMENT_REQUEST,
            self::CANCEL_REQUEST,
            self::RETURN_REQUEST,
            self::PICKUP_REQUEST,
            self::USER_UPDATE,
        ];
    }
}
