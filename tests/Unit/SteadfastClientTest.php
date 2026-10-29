<?php

namespace AhRaihan\SteadfastCourier\Tests\Unit;

use AhRaihan\SteadfastCourier\SteadfastClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class SteadfastClientTest extends TestCase
{
    protected SteadfastClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new SteadfastClient(
            apiKey: 'mock_api_key',
            secretKey: 'mock_secret_key',
            baseUrl: 'https://portal.packzy.com/api/v1',
            webhookToken: 'mock_webhook_token'
        );
    }

    public function test_ping_endpoint(): void
    {
        Http::fake([
            '*/ping' => Http::response([
                'status' => 200,
                'message' => 'pong',
                'service' => 'steadfast-api',
                'version' => 'v1',
            ], 200),
        ]);

        $response = $this->client->ping();

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('pong', $response['message']);
    }

    public function test_create_order(): void
    {
        Http::fake([
            '*/create_order' => Http::response([
                'status' => 200,
                'message' => 'Consignment has been created successfully.',
                'consignment' => [
                    'consignment_id' => 1424107,
                    'invoice' => 'ORD-10231',
                    'tracking_code' => '15BAEB8A',
                ],
            ], 200),
        ]);

        $orderData = [
            'invoice' => 'ORD-10231',
            'recipient_name' => 'Jahid Hasan',
            'recipient_phone' => '01712345678',
            'recipient_address' => 'House 17/1, Dhanmondi',
            'cod_amount' => 1060,
        ];

        $response = $this->client->createOrder($orderData);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals(1424107, $response['consignment']['consignment_id']);
        $this->assertEquals('15BAEB8A', $response['consignment']['tracking_code']);
    }

    public function test_bulk_create_orders(): void
    {
        Http::fake([
            '*/create_order/bulk-order' => Http::response([
                'status' => 200,
                'message' => 'We have a response for you.',
                'data' => [
                    [
                        'invoice' => 'ORD-10231',
                        'consignment_id' => 1424107,
                        'status' => 'success',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->client->createBulkOrders([
            [
                'invoice' => 'ORD-10231',
                'recipient_name' => 'Jahid Hasan',
                'recipient_phone' => '01712345678',
                'recipient_address' => 'Dhanmondi, Dhaka',
                'cod_amount' => 1060,
            ],
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals(1424107, $response['data'][0]['consignment_id']);
    }

    public function test_bulk_create_orders_extended(): void
    {
        Http::fake([
            '*/create_order/bulk-order/extended' => Http::response([
                'status' => 200,
                'message' => 'Processed bulk order entries.',
                'data' => [
                    [
                        'invoice' => 'ORD-10232',
                        'status' => 'error',
                        'error' => ['The recipient phone format is invalid.'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->client->createBulkOrdersExtended([
            [
                'invoice' => 'ORD-10232',
                'recipient_name' => 'Nusrat',
                'recipient_phone' => '018',
                'recipient_address' => 'Dhaka',
                'cod_amount' => 500,
            ],
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('error', $response['data'][0]['status']);
    }

    public function test_get_status_by_consignment_id(): void
    {
        Http::fake([
            '*/status_by_cid/*' => Http::response([
                'status' => 200,
                'delivery_status' => 'delivered',
            ], 200),
        ]);

        $response = $this->client->getStatusByConsignmentId(1424107);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('delivered', $response['delivery_status']);
    }

    public function test_get_status_with_return_status(): void
    {
        Http::fake([
            '*/status_with_return_status_by_cid/*' => Http::response([
                'status' => 200,
                'delivery_status' => 'cancelled_return_rider_assigned',
            ], 200),
        ]);

        $response = $this->client->getStatusWithReturnStatusByConsignmentId(1424107);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('cancelled_return_rider_assigned', $response['delivery_status']);
    }

    public function test_get_status_by_invoice(): void
    {
        Http::fake([
            '*/status_by_invoice/*' => Http::response([
                'status' => 200,
                'delivery_status' => 'pending',
            ], 200),
        ]);

        $response = $this->client->getStatusByInvoice('ORD-10231');

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('pending', $response['delivery_status']);
    }

    public function test_get_status_by_tracking_code(): void
    {
        Http::fake([
            '*/status_by_trackingcode/*' => Http::response([
                'status' => 200,
                'delivery_status' => 'in_review',
            ], 200),
        ]);

        $response = $this->client->getStatusByTrackingCode('15BAEB8A');

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('in_review', $response['delivery_status']);
    }

    public function test_get_trackings_by_invoice(): void
    {
        Http::fake([
            '*/trackings_by_invoice/*' => Http::response([
                'status' => 200,
                'tracking' => [
                    [
                        'consignment_id' => 1424107,
                        'tracking_type' => 1,
                        'text' => 'Consignment created by Sender(API).',
                        'created_at' => '2026-09-20T07:05:31.000000Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->client->getTrackingsByInvoice('ORD-10231');

        $this->assertEquals(200, $response['status']);
        $this->assertCount(1, $response['tracking']);
        $this->assertEquals('Consignment created by Sender(API).', $response['tracking'][0]['text']);
    }

    public function test_create_pickup_request(): void
    {
        Http::fake([
            '*/create_pickup_request' => Http::response([
                'message' => 'Pickup request created successfully.',
                'data' => [
                    'id' => 9081,
                    'address_id' => 42,
                    'police_station_id' => 17,
                ],
            ], 201),
        ]);

        $response = $this->client->createPickupRequest([
            'address_id' => 42,
            'police_station_id' => 17,
            'address' => 'Dhanmondi',
            'contact_number' => '01712345678',
            'estim_qty' => 10,
        ]);

        $this->assertEquals(9081, $response['data']['id']);
    }

    public function test_create_return_request(): void
    {
        Http::fake([
            '*/create_return_request' => Http::response([
                'id' => 1,
                'consignment_id' => 1424107,
                'reason' => 'Customer changed mind',
                'status' => 'pending',
            ], 201),
        ]);

        $response = $this->client->createReturnRequest([
            'invoice' => 'ORD-10231',
            'reason' => 'Customer changed mind',
        ]);

        $this->assertEquals(1, $response['id']);
        $this->assertEquals('pending', $response['status']);
    }

    public function test_get_return_requests(): void
    {
        Http::fake([
            '*/get_return_requests*' => Http::response([
                'current_page' => 1,
                'data' => [],
            ], 200),
        ]);

        $response = $this->client->getReturnRequests(1);

        $this->assertEquals(1, $response['current_page']);
    }

    public function test_get_current_balance(): void
    {
        Http::fake([
            '*/get_balance' => Http::response([
                'status' => 200,
                'current_balance' => 12450,
            ], 200),
        ]);

        $response = $this->client->getCurrentBalance();

        $this->assertEquals(200, $response['status']);
        $this->assertEquals(12450, $response['current_balance']);
    }

    public function test_get_payments_and_single_payment(): void
    {
        Http::fake([
            '*/payments/88213' => Http::response([
                'payment_id' => 'SFC-88213',
                'amount' => 12450,
            ], 200),
            '*/payments*' => Http::response([
                'status' => 1,
                'payments' => [
                    ['payment_id' => 'SFC-88213', 'amount' => 12450],
                ],
            ], 200),
        ]);

        $list = $this->client->getPayments();
        $this->assertEquals(1, $list['status']);

        $single = $this->client->getPayment('SFC-88213');
        $this->assertEquals(12450, $single['amount']);
    }

    public function test_get_police_stations(): void
    {
        Http::fake([
            '*/police_stations' => Http::response([
                ['id' => 1, 'name' => 'Dhanmondi', 'district' => 'Dhaka'],
            ], 200),
        ]);

        $response = $this->client->getPoliceStations();

        $this->assertEquals('Dhanmondi', $response[0]['name']);
    }

    public function test_fraud_check(): void
    {
        Http::fake([
            '*/fraud_check/score/*' => Http::response([
                'status' => 200,
                'phone' => '01712345678',
                'delivery_ratio' => 92,
                'cancellation_ratio' => 7,
                'volume_band' => 'high',
            ], 200),
        ]);

        $response = $this->client->checkFraud('01712345678');

        $this->assertEquals(200, $response['status']);
        $this->assertEquals(92, $response['delivery_ratio']);
        $this->assertEquals('high', $response['volume_band']);
    }

    public function test_with_config_immutability(): void
    {
        $customClient = $this->client->withConfig('new_key', 'new_secret');

        $this->assertNotSame($this->client, $customClient);
    }

    public function test_webhook_signature_verification(): void
    {
        $rawPayload = json_encode(['notification_type' => 'delivery_status', 'consignment_id' => 12345]);
        $token = 'my_secret_webhook_token';

        $signature = hash_hmac('sha256', $rawPayload, $token);

        $this->assertTrue($this->client->verifyWebhookSignature($rawPayload, $signature, $token));
        $this->assertFalse($this->client->verifyWebhookSignature($rawPayload, 'invalid_signature_hex', $token));
        $this->assertFalse($this->client->verifyWebhookSignature($rawPayload, null, $token));
    }
}
