<?php

namespace AhRaihan\SteadfastCourier\Helpers;

class Sanitizer
{
    /**
     * Characters that Steadfast strips and replaces with a space:
     * { } ; < > $
     */
    protected const STRIP_CHARS = ['{', '}', ';', '<', '>', '$'];

    /**
     * Replace restricted characters with a space.
     */
    public static function strip(string $value): string
    {
        return str_replace(self::STRIP_CHARS, ' ', $value);
    }

    /**
     * Safely truncate a string to the given length.
     */
    public static function truncate(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }

    /**
     * Clean and format phone number according to Steadfast requirements:
     * 11 digits beginning with 01 (or 013-019).
     */
    public static function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Strip Bangladesh country code if present (8801... -> 01...)
        if (str_starts_with($cleaned, '8801')) {
            $cleaned = substr($cleaned, 2);
        } elseif (str_starts_with($cleaned, '880')) {
            $cleaned = '0'.substr($cleaned, 3);
        }

        return self::truncate($cleaned, 40);
    }

    /**
     * Ensure invoice contains only allowed characters (letters, digits, hyphens, underscores).
     */
    public static function formatInvoice(string $invoice): string
    {
        $cleaned = preg_replace('/[^A-Za-z0-9_-]/', '', $invoice);

        return self::truncate($cleaned, 100);
    }

    /**
     * Sanitize and format full order payload before sending to Steadfast.
     */
    public static function sanitizeOrderPayload(array $data): array
    {
        if (isset($data['invoice'])) {
            $data['invoice'] = self::formatInvoice((string) $data['invoice']);
        }

        if (isset($data['recipient_name'])) {
            $data['recipient_name'] = self::truncate(trim(self::strip((string) $data['recipient_name'])), 100);
        }

        if (isset($data['recipient_phone'])) {
            $data['recipient_phone'] = self::formatPhone((string) $data['recipient_phone']);
        }

        if (isset($data['recipient_address'])) {
            $data['recipient_address'] = self::truncate(trim(self::strip((string) $data['recipient_address'])), 490);
        }

        if (isset($data['note'])) {
            $data['note'] = self::truncate(trim(self::strip((string) $data['note'])), 480);
        }

        if (isset($data['alternative_phone']) && ! empty($data['alternative_phone'])) {
            $data['alternative_phone'] = self::formatPhone((string) $data['alternative_phone']);
        }

        if (isset($data['item_description'])) {
            $data['item_description'] = self::truncate(trim(self::strip((string) $data['item_description'])), 255);
        }

        if (isset($data['cod_amount'])) {
            $data['cod_amount'] = (float) $data['cod_amount'];
        }

        if (isset($data['total_lot'])) {
            $data['total_lot'] = (int) $data['total_lot'];
        }

        if (isset($data['delivery_type'])) {
            $data['delivery_type'] = (int) $data['delivery_type'];
        }

        return $data;
    }
}
