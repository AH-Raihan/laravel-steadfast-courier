# Steadfast Courier (API + Webhook) for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahraihan/laravel-steadfast-courier.svg?style=flat-square)](https://packagist.org/packages/ahraihan/laravel-steadfast-courier)
[![Total Downloads](https://img.shields.io/packagist/dt/ahraihan/laravel-steadfast-courier.svg?style=flat-square)](https://packagist.org/packages/ahraihan/laravel-steadfast-courier)
[![License](https://img.shields.io/github/license/dev-ahraihan/laravel-steadfast-courier.svg?style=flat-square)](LICENSE.md)

A complete, battle-tested, and developer-friendly Laravel package for **Steadfast Courier API (all 18 endpoints)** and **Webhook notifications** with HMAC-SHA256 signature verification and Idempotency handling.

---

## 🚀 Features

- **Full API Coverage (All 18 Official Endpoints)**:
  - Connectivity check (`ping`)
  - Single order booking (`create_order`)
  - Bulk orders booking (`create_order/bulk-order`)
  - Extended bulk orders with detailed per-field validation (`create_order/bulk-order/extended`)
  - Status tracking by Consignment ID, Tracking Code, and Merchant Invoice
  - Detailed chronological tracking history timeline
  - Return requests creation & pagination
  - Pickup rider requests
  - Real-time account balance & payout reconciliation
  - Thanas / Police Stations directory
  - Customer delivery record & fraud check score
- **Full Webhook Support**:
  - Automatic webhook route registration
  - Secure HMAC-SHA256 (`X-Signature`) verification with timingSafeEqual comparison
  - `Idempotency-Key` tracking to eliminate duplicate event processing on Steadfast retries
  - Dispatches strongly-typed Laravel Events for each notification type (`delivery_status`, `tracking_update`, `consignment_update`, `return_list_accepted`, `payment_request`, `cancel_request`, `return_request`, `pickup_request`, `user_update`)
- **Smart Sanitization**:
  - Automatically strips restricted characters (`{`, `}`, `;`, `<`, `>`, `$`) according to Steadfast docs
  - Automatically formats Bangladeshi phone numbers (`01...`) and trims fields to max lengths
- **Clean Laravel Ergonomics**:
  - Facade support (`Steadfast::...` and `SteadfastCourier::...`)
  - Multi-tenant / Dynamic credentials support (`withConfig(...)`)
  - 100% backward compatibility with legacy Steadfast Courier package methods

---

## 📦 Requirements

- PHP `^8.2`, `^8.3`, or `^8.4`
- Laravel `10.x`, `11.x`, `12.x`, or `13.x`

---

## 🛠 Installation

Install the package via Composer:

```bash
composer require ahraihan/laravel-steadfast-courier
```

The package uses Laravel package auto-discovery, so its Service Provider and Facade aliases are registered automatically.

### Publish Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=steadfast-config
```

This creates `config/steadfast.php`.

---

## ⚙️ Configuration

Add your Steadfast credentials to your `.env` file:

```dotenv
# Steadfast Courier API
STEADFAST_BASE_URL=https://portal.packzy.com/api/v1
STEADFAST_API_KEY=your_steadfast_api_key
STEADFAST_SECRET_KEY=your_steadfast_secret_key
STEADFAST_TIMEOUT=30

# Steadfast Webhook
STEADFAST_WEBHOOK_ENABLED=true
STEADFAST_WEBHOOK_PATH=steadfast/webhook
STEADFAST_WEBHOOK_TOKEN=your_webhook_auth_token_from_steadfast
STEADFAST_WEBHOOK_VERIFY_SIGNATURE=true
STEADFAST_WEBHOOK_IDEMPOTENCY=true
```

---

## 📖 API Usage

Import the Facade into your controllers, jobs, or services:

```php
use AhRaihan\SteadfastCourier\Facades\Steadfast;
```

*(Note: `use AhRaihan\SteadfastCourier\Facades\SteadfastCourier;` is also available and 100% interchangeable).*

### 1. Service Check (`/ping`)
Verify that the Steadfast server is reachable (requires no credentials).

```php
$response = Steadfast::ping();
// ['status' => 200, 'message' => 'pong', 'service' => 'steadfast-api', 'version' => 'v1', ...]
```

### 2. Book Single Parcel (`/create_order`)

```php
$response = Steadfast::createOrder([
    'invoice'           => 'ORD-10231',               // Required. Alphanumeric, dash, underscore only (max 100)
    'recipient_name'    => 'Jahid Hasan',             // Required (max 100)
    'recipient_phone'   => '01712345678',             // Required. 11 digits starting 01
    'recipient_address' => 'House 17/1, Dhanmondi',   // Required (max 490)
    'cod_amount'        => 1060,                      // Required. BDT (0 for prepaid)
    'note'              => 'Call before 3 PM',        // Optional (max 480)
    'alternative_phone' => '01812345678',             // Optional. 11 digits starting 01
    'recipient_email'   => 'customer@example.com',    // Optional
    'item_description'  => 'Books and Stationery',    // Optional (max 255)
    'total_lot'         => 1,                         // Optional (default: 1)
    'delivery_type'     => 0,                         // Optional. 0 = Home Delivery (default), 1 = Point Delivery
]);

$consignmentId = $response['consignment']['consignment_id'] ?? null;
$trackingCode  = $response['consignment']['tracking_code'] ?? null;
$trackingLink  = $response['consignment']['tracking_link'] ?? null;
```

### 3. Bulk Order Booking (`/create_order/bulk-order`)
Book up to 500 parcels in a single call.

```php
$orders = [
    [
        'invoice'           => 'ORD-10231',
        'recipient_name'    => 'Jahid Hasan',
        'recipient_phone'   => '01712345678',
        'recipient_address' => 'Dhanmondi, Dhaka',
        'cod_amount'        => 1060,
    ],
    [
        'invoice'           => 'ORD-10232',
        'recipient_name'    => 'Nusrat Jahan',
        'recipient_phone'   => '01812345678',
        'recipient_address' => 'Uttara, Dhaka',
        'cod_amount'        => 450,
    ],
];

$response = Steadfast::createBulkOrders($orders);
```

### 4. Extended Bulk Orders (`/create_order/bulk-order/extended`)
Preferred bulk booking endpoint: returns clear, human-readable per-field error sentences.

```php
$response = Steadfast::createBulkOrdersExtended($orders);
```

### 5. Check Status by Consignment ID (`/status_by_cid/{id}`)

```php
$response = Steadfast::getStatusByConsignmentId(1424107);
// ['status' => 200, 'delivery_status' => 'delivered']
```

### 6. Check Status with Return Status (`/status_with_return_status_by_cid/{id}`)
Essential for stock reconciliation: reveals whether a returned parcel is back with you.

```php
$response = Steadfast::getStatusWithReturnStatusByConsignmentId(1424107);
// ['status' => 200, 'delivery_status' => 'cancelled_return_received']
```

### 7. Check Status by Invoice ID (`/status_by_invoice/{invoice}`)

```php
$response = Steadfast::getStatusByInvoice('ORD-10231');
```

### 8. Check Status by Tracking Code (`/status_by_trackingcode/{code}`)

```php
$response = Steadfast::getStatusByTrackingCode('15BAEB8A');
```

### 9. Detailed Tracking Steps History (`/trackings_by_invoice/{invoice}`)
Shows every milestone the parcel has been through.

```php
$response = Steadfast::getTrackingsByInvoice('ORD-10231');

foreach ($response['tracking'] as $step) {
    echo $step['text'].' at '.$step['created_at'];
}
```

### 10. Request Rider Pickup (`/create_pickup_request`)

```php
$response = Steadfast::createPickupRequest([
    'address_id'        => 42,
    'police_station_id' => 17,
    'address'           => 'House 17/1, Road 3/A, Dhanmondi',
    'contact_number'    => '01712345678',
    'estim_qty'         => 25,
    'note'              => 'Pick up after 2 PM',
]);
```

### 11. Request Parcel Return (`/create_return_request`)

```php
$response = Steadfast::createReturnRequest([
    'invoice' => 'ORD-10231', // or 'consignment_id' or 'tracking_code'
    'reason'  => 'Customer changed mind before delivery',
]);
```

### 12. List Return Requests (`/get_return_requests`)

```php
$response = Steadfast::getReturnRequests(page: 1);
```

### 13. Get Single Return Request (`/get_return_request/{id}`)

```php
$response = Steadfast::getReturnRequest(1);
```

### 14. Get Merchant Current Balance (`/get_balance`)

```php
$balance = Steadfast::getCurrentBalance();
$amount = $balance['current_balance']; // e.g. 12450
```

### 15. List Payments / Payouts (`/payments`)

```php
$payments = Steadfast::getPayments(page: 1);
```

### 16. Get Payment Detail with Settled Parcels (`/payments/{id}`)

```php
$payout = Steadfast::getPayment('SFC-88213'); // Accepts with or without SFC- prefix
```

### 17. List Police Stations / Thanas (`/police_stations`)

```php
$stations = Steadfast::getPoliceStations();
```

### 18. Customer Fraud Check & Delivery Record (`/fraud_check/score/{phone}`)

```php
$record = Steadfast::checkFraud('01712345678');

$deliveryRatio     = $record['delivery_ratio'];     // Percentage delivered (e.g. 92)
$cancellationRatio = $record['cancellation_ratio']; // Percentage cancelled (e.g. 7)
$volumeBand        = $record['volume_band'];        // low, medium, high, very_high
$deliveredCount    = $record['delivered_count'];    // Exact count (if available)
$cancelledCount    = $record['cancelled_count'];    // Exact count (if available)
```

---

## 🔀 Dynamic / Multi-Store Credentials

If your system supports multiple merchant accounts or stores:

```php
$client = Steadfast::withConfig(
    apiKey: 'another_api_key',
    secretKey: 'another_secret_key',
    baseUrl: 'https://portal.packzy.com/api/v1',
    webhookToken: 'another_webhook_token'
);

$balance = $client->getCurrentBalance();
```

---

## 🔔 Webhook Integration Guide

Steadfast posts webhook events to your application when a parcel's status updates, a payout is requested, or tracking progress occurs.

### 1. Set Up Webhook Endpoint in Steadfast Dashboard

1. Log in to your Steadfast Courier Dashboard.
2. Go to **Webhook** settings.
3. In **Callback URL**, enter: `https://your-domain.com/steadfast/webhook`.
4. Click **Generate** next to **Auth token**.
5. Copy the generated token and save it into your `.env`:
   ```dotenv
   STEADFAST_WEBHOOK_TOKEN=your_generated_token_here
   ```
6. Click **Save webhook**.

### 2. Exclude Webhook Route from CSRF Protection

Because Steadfast posts directly to your webhook URL, exclude it from CSRF verification:

#### Laravel 11 & 12 (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'steadfast/webhook',
    ]);
})
```

#### Laravel 10 (`app/Http/Middleware/VerifyCsrfToken.php`):
```php
protected $except = [
    'steadfast/webhook',
];
```

### 3. Listen to Webhook Events

When Steadfast triggers the webhook, the package automatically verifies the HMAC-SHA256 signature and fires the corresponding Laravel event.

Register event listeners in your `EventServiceProvider` or `AppServiceProvider`:

```php
use AhRaihan\SteadfastCourier\Events\SteadfastDeliveryStatusChanged;
use AhRaihan\SteadfastCourier\Events\SteadfastTrackingUpdated;
use AhRaihan\SteadfastCourier\Events\SteadfastWebhookReceived;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    // Listen for parcel delivery status changes
    Event::listen(SteadfastDeliveryStatusChanged::class, function (SteadfastDeliveryStatusChanged $event) {
        $invoice        = $event->invoice;            // 'ORD-10231'
        $consignmentId  = $event->consignmentId;      // 12345
        $status         = $event->status;             // 'delivered', 'cancelled', 'partial_delivered', etc.
        $codAmount      = $event->codAmount;          // 1500.0
        $deliveryCharge = $event->deliveryCharge;     // 100.0
        $message        = $event->trackingMessage;    // 'Your package has been delivered successfully.'
        $updatedAt      = $event->updatedAt;          // '2026-03-02 12:45:30'

        if ($event->isDelivered()) {
            // Update order in database to completed / mark paid
        } elseif ($event->isCancelled()) {
            // Restock items or notify support
        }
    });

    // Listen to all webhook events (generic)
    Event::listen(SteadfastWebhookReceived::class, function (SteadfastWebhookReceived $event) {
        Log::info('Steadfast Webhook: ' . $event->notificationType, $event->payload);
    });
}
```

### Available Webhook Events

| Notification Type (`notification_type`) | Laravel Event Dispatched | Description |
|---|---|---|
| `*` (any event) | `SteadfastWebhookReceived` | General event containing raw payload & signature |
| `delivery_status` | `SteadfastDeliveryStatusChanged` | Parcel delivery status updated |
| `tracking_update` | `SteadfastTrackingUpdated` | New tracking step or transit milestone |
| `consignment_update` | `SteadfastConsignmentUpdated` | Consignment updated (address, COD, etc.) |
| `return_list_accepted` | `SteadfastReturnListAccepted` | Return list receipt confirmed |
| `payment_request` | `SteadfastPaymentRequested` | Payout requested |
| `cancel_request` | `SteadfastCancelRequested` | Cancellation request raised/decided |
| `return_request` | `SteadfastReturnRequested` | Return request raised |
| `pickup_request` | `SteadfastPickupRequested` | Pickup requested |
| `user_update` | `SteadfastUserUpdated` | Account details updated |

---

## 🧪 Testing

Run package tests:

```bash
composer test
# or
vendor/bin/pest
```

---

## 🚀 How to Publish to GitHub and Packagist

To publish this package so that anyone (or your other projects) can install it via `composer require ahraihan/laravel-steadfast-courier`:

1. **Initialize Git inside package directory**:
   ```bash
   cd packages/ahraihan/laravel-steadfast-courier
   git init
   git add .
   git commit -m "feat: initial release of Steadfast Courier Laravel package"
   ```

2. **Push to GitHub**:
   - Create a new public repository on GitHub: `https://github.com/dev-ahraihan/laravel-steadfast-courier`
   ```bash
   git remote add origin https://github.com/dev-ahraihan/laravel-steadfast-courier.git
   git branch -M main
   git push -u origin main
   ```

3. **Tag a Release**:
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin v1.0.0
   ```

4. **Submit to Packagist**:
   - Visit [packagist.org](https://packagist.org/) and log in.
   - Click **Submit**.
   - Enter your repository URL: `https://github.com/dev-ahraihan/laravel-steadfast-courier`.
   - Click **Check** and then **Submit**.
   - Set up the GitHub Webhook on Packagist for automatic release updates!

---

## 📄 License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

## 👤 Author

**AH-Raihan**
- Email: [dev.ahraihan@gmail.com](mailto:dev.ahraihan@gmail.com)
- GitHub: [@dev-ahraihan](https://github.com/dev-ahraihan)

