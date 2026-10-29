<?php

namespace AhRaihan\SteadfastCourier;

use AhRaihan\SteadfastCourier\Helpers\Sanitizer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SteadfastClient
{
    /**
     * Steadfast API base URL.
     */
    protected string $baseUrl;

    /**
     * Steadfast API key.
     */
    protected string $apiKey;

    /**
     * Steadfast Secret key.
     */
    protected string $secretKey;

    /**
     * Webhook authentication token.
     */
    protected ?string $webhookToken;

    /**
     * Request timeout in seconds.
     */
    protected int $timeout;

    /**
     * Create a new Steadfast Client instance.
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $secretKey = null,
        ?string $baseUrl = null,
        ?string $webhookToken = null,
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey ?? (string) config('steadfast.api_key', config('steadfast-courier.api_key', ''));
        $this->secretKey = $secretKey ?? (string) config('steadfast.secret_key', config('steadfast-courier.secret_key', ''));
        $this->baseUrl = $baseUrl ?? (string) config('steadfast.base_url', config('steadfast-courier.base_url', 'https://portal.packzy.com/api/v1'));
        $this->webhookToken = $webhookToken ?? (string) config('steadfast.webhook.auth_token', '');
        $this->timeout = $timeout ?: (int) config('steadfast.timeout', 30);
    }

    /**
     * Set dynamic credentials and return a cloned instance.
     */
    public function withConfig(string $apiKey, string $secretKey, ?string $baseUrl = null, ?string $webhookToken = null): self
    {
        $clone = clone $this;
        $clone->apiKey = $apiKey;
        $clone->secretKey = $secretKey;

        if ($baseUrl !== null) {
            $clone->baseUrl = $baseUrl;
        }

        if ($webhookToken !== null) {
            $clone->webhookToken = $webhookToken;
        }

        return $clone;
    }

    /**
     * Check if the client has API credentials configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->secretKey);
    }

    /**
     * 1. Service check: GET /ping
     * The only endpoint that needs no credentials. Checks reachability.
     */
    public function ping(): array
    {
        $url = rtrim($this->baseUrl, '/').'/ping';

        $response = Http::timeout($this->timeout)->get($url);

        return $this->formatResponse($response);
    }

    /**
     * 2. Booking a parcel: POST /create_order
     * Stored to 100 chars (invoice, name), 40 (phone), 490 (address), 480 (note).
     */
    public function createOrder(array $data, bool $autoSanitize = true): array
    {
        $payload = $autoSanitize ? Sanitizer::sanitizeOrderPayload($data) : $data;

        return $this->post('/create_order', $payload);
    }

    /**
     * 3. Bulk order booking: POST /create_order/bulk-order
     * Book up to 500 parcels in one request.
     */
    public function createBulkOrders(array $orders, bool $autoSanitize = true): array
    {
        if ($autoSanitize) {
            $orders = array_map([Sanitizer::class, 'sanitizeOrderPayload'], $orders);
        }

        return $this->post('/create_order/bulk-order', [
            'data' => $orders,
        ]);
    }

    /**
     * 4. Extended bulk booking: POST /create_order/bulk-order/extended
     * Preferred bulk endpoint: returns per-field validation sentences instead of codes.
     */
    public function createBulkOrdersExtended(array $orders, bool $autoSanitize = true): array
    {
        if ($autoSanitize) {
            $orders = array_map([Sanitizer::class, 'sanitizeOrderPayload'], $orders);
        }

        return $this->post('/create_order/bulk-order/extended', [
            'data' => $orders,
        ]);
    }

    /**
     * 5. Check delivery status by Consignment ID: GET /status_by_cid/{consignment_id}
     */
    public function getStatusByConsignmentId(int|string $consignmentId): array
    {
        return $this->get('/status_by_cid/'.urlencode((string) $consignmentId));
    }

    /**
     * 6. Check delivery status with return status by CID: GET /status_with_return_status_by_cid/{consignment_id}
     * Shows return progress if parcel is returning.
     */
    public function getStatusWithReturnStatusByConsignmentId(int|string $consignmentId): array
    {
        return $this->get('/status_with_return_status_by_cid/'.urlencode((string) $consignmentId));
    }

    /**
     * 7. Check delivery status by Merchant Invoice ID: GET /status_by_invoice/{invoice}
     */
    public function getStatusByInvoice(string $invoice): array
    {
        return $this->get('/status_by_invoice/'.urlencode($invoice));
    }

    /**
     * 8. Check delivery status by Tracking Code: GET /status_by_trackingcode/{tracking_code}
     */
    public function getStatusByTrackingCode(string $trackingCode): array
    {
        return $this->get('/status_by_trackingcode/'.urlencode($trackingCode));
    }

    /**
     * 9. Detailed tracking history: GET /trackings_by_invoice/{invoice}
     * Returns full chronological timeline of events for the parcel.
     */
    public function getTrackingsByInvoice(string $invoice): array
    {
        return $this->get('/trackings_by_invoice/'.urlencode($invoice));
    }

    /**
     * 10. Request rider pickup: POST /create_pickup_request
     * Fields: address_id, police_station_id, address, contact_number, note, estim_qty
     */
    public function createPickupRequest(array $data): array
    {
        if (isset($data['contact_number'])) {
            $data['contact_number'] = Sanitizer::formatPhone((string) $data['contact_number']);
        }

        if (isset($data['address'])) {
            $data['address'] = Sanitizer::truncate(Sanitizer::strip((string) $data['address']), 255);
        }

        if (isset($data['note'])) {
            $data['note'] = Sanitizer::truncate(Sanitizer::strip((string) $data['note']), 500);
        }

        return $this->post('/create_pickup_request', $data);
    }

    /**
     * 11. Request parcel return: POST /create_return_request
     * Fields: consignment_id, invoice, or tracking_code, plus optional reason.
     */
    public function createReturnRequest(array $data): array
    {
        if (isset($data['reason'])) {
            $data['reason'] = Sanitizer::truncate(Sanitizer::strip((string) $data['reason']), 500);
        }

        return $this->post('/create_return_request', $data);
    }

    /**
     * 12. List return requests (paginated, 10 per page): GET /get_return_requests?page={page}
     */
    public function getReturnRequests(int $page = 1): array
    {
        return $this->get('/get_return_requests', ['page' => $page]);
    }

    /**
     * 13. Get single return request by ID: GET /get_return_request/{id}
     */
    public function getReturnRequest(int|string $id): array
    {
        return $this->get('/get_return_request/'.urlencode((string) $id));
    }

    /**
     * 14. Get merchant current balance: GET /get_balance
     * Delivered COD, less delivery charges, less 1% collection fee.
     */
    public function getCurrentBalance(): array
    {
        return $this->get('/get_balance');
    }

    /**
     * 15. Get merchant payouts (paginated, 10 per page): GET /payments?page={page}
     */
    public function getPayments(int $page = 1): array
    {
        return $this->get('/payments', ['page' => $page]);
    }

    /**
     * 16. Get payout detail: GET /payments/{payment_id}
     * Shows all settled parcels for this payout. Accepts ID with or without 'SFC-' prefix.
     */
    public function getPayment(int|string $paymentId): array
    {
        $cleanedId = preg_replace('/[^0-9]/', '', (string) $paymentId);

        return $this->get('/payments/'.urlencode($cleanedId ?: (string) $paymentId));
    }

    /**
     * 17. List of police stations / thanas: GET /police_stations
     */
    public function getPoliceStations(): array
    {
        return $this->get('/police_stations');
    }

    /**
     * 18. Fraud check customer delivery score: GET /fraud_check/score/{phone}
     * Returns delivery_ratio, cancellation_ratio, volume_band, volume_range, etc.
     */
    public function checkFraud(string $phone): array
    {
        $cleanedPhone = Sanitizer::formatPhone($phone);

        return $this->get('/fraud_check/score/'.urlencode($cleanedPhone));
    }

    // ==========================================
    // Backward Compatibility Helper Methods
    // ==========================================

    /**
     * Alias for createOrder (matches old SteadfastCourier package).
     */
    public function placeOrder(array $data): array
    {
        return $this->createOrder($data);
    }

    /**
     * Alias for createBulkOrders (matches old SteadfastCourier package).
     */
    public function bulkCreateOrders($data): array
    {
        $orders = is_string($data) ? json_decode($data, true) : $data;

        return $this->createBulkOrders((array) $orders);
    }

    /**
     * Alias for getStatusByConsignmentId.
     */
    public function checkDeliveryStatusByConsignmentId(int|string $id): array
    {
        return $this->getStatusByConsignmentId($id);
    }

    /**
     * Alias for getStatusByInvoice.
     */
    public function checkDeliveryStatusByInvoiceId(string $id): array
    {
        return $this->getStatusByInvoice($id);
    }

    /**
     * Alias for getStatusByTrackingCode.
     */
    public function checkDeliveryStatusByTrackingCode(string $code): array
    {
        return $this->getStatusByTrackingCode($code);
    }

    // ==========================================
    // Webhook Signature Verification
    // ==========================================

    /**
     * Compute HMAC-SHA256 signature for webhook payload.
     */
    public function computeWebhookSignature(string $rawBody, ?string $token = null): string
    {
        $secret = $token ?? $this->webhookToken;

        return hash_hmac('sha256', $rawBody, (string) $secret);
    }

    /**
     * Verify incoming webhook request signature using timingSafeEqual comparison.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature, ?string $token = null): bool
    {
        if (empty($signature)) {
            return false;
        }

        $expected = $this->computeWebhookSignature($rawBody, $token);

        return hash_equals($expected, $signature);
    }

    // ==========================================
    // Internal HTTP Request Execution
    // ==========================================

    /**
     * Execute a GET request.
     */
    protected function get(string $path, array $query = []): array
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');

        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->get($url, $query);

        return $this->formatResponse($response);
    }

    /**
     * Execute a POST request.
     */
    protected function post(string $path, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');

        $response = Http::withHeaders($this->getHeaders())
            ->timeout($this->timeout)
            ->post($url, $data);

        return $this->formatResponse($response);
    }

    /**
     * Common headers for Steadfast API calls.
     */
    protected function getHeaders(): array
    {
        return [
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Format and return standard array response.
     */
    protected function formatResponse(Response $response): array
    {
        $data = $response->json();

        if (is_array($data)) {
            return $data;
        }

        return [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->body(),
        ];
    }
}
