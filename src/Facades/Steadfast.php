<?php

namespace AhRaihan\SteadfastCourier\Facades;

use AhRaihan\SteadfastCourier\SteadfastClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array ping()
 * @method static array createOrder(array $data, bool $autoSanitize = true)
 * @method static array createBulkOrders(array $orders, bool $autoSanitize = true)
 * @method static array createBulkOrdersExtended(array $orders, bool $autoSanitize = true)
 * @method static array getStatusByConsignmentId(int|string $consignmentId)
 * @method static array getStatusWithReturnStatusByConsignmentId(int|string $consignmentId)
 * @method static array getStatusByInvoice(string $invoice)
 * @method static array getStatusByTrackingCode(string $trackingCode)
 * @method static array getTrackingsByInvoice(string $invoice)
 * @method static array createPickupRequest(array $data)
 * @method static array createReturnRequest(array $data)
 * @method static array getReturnRequests(int $page = 1)
 * @method static array getReturnRequest(int|string $id)
 * @method static array getCurrentBalance()
 * @method static array getPayments(int $page = 1)
 * @method static array getPayment(int|string $paymentId)
 * @method static array getPoliceStations()
 * @method static array checkFraud(string $phone)
 * @method static \AhRaihan\SteadfastCourier\SteadfastClient withConfig(string $apiKey, string $secretKey, ?string $baseUrl = null, ?string $webhookToken = null)
 * @method static bool verifyWebhookSignature(string $rawBody, ?string $signature, ?string $token = null)
 * @method static string computeWebhookSignature(string $rawBody, ?string $token = null)
 * @method static bool isConfigured()
 * @method static array placeOrder(array $data)
 * @method static array bulkCreateOrders(array|string $data)
 * @method static array checkDeliveryStatusByConsignmentId(int|string $id)
 * @method static array checkDeliveryStatusByInvoiceId(string $id)
 * @method static array checkDeliveryStatusByTrackingCode(string $code)
 *
 * @see SteadfastClient
 */
class Steadfast extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'steadfast';
    }
}
