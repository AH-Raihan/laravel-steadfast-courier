<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Steadfast API Credentials
    |--------------------------------------------------------------------------
    |
    | API Key and Secret Key issued by Steadfast Courier.
    | Obtain these from your Steadfast merchant dashboard.
    |
    */

    'base_url' => env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'),

    'api_key' => env('STEADFAST_API_KEY', ''),

    'secret_key' => env('STEADFAST_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for HTTP requests to Steadfast API.
    |
    */

    'timeout' => (int) env('STEADFAST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Steadfast sends POST JSON requests when parcel statuses or events update.
    | Configure the URL endpoint, authentication token, and signature verification.
    |
    */

    'webhook' => [
        /**
         * Enable or disable automatic webhook route registration.
         */
        'enabled' => (bool) env('STEADFAST_WEBHOOK_ENABLED', true),

        /**
         * The URI route path where Steadfast will send webhook notifications.
         * e.g. 'steadfast/webhook' produces https://your-domain.com/steadfast/webhook
         */
        'url_path' => env('STEADFAST_WEBHOOK_PATH', 'steadfast/webhook'),

        /**
         * Webhook Auth Token generated in the Steadfast Webhook dashboard.
         * Sent in Authorization: Bearer <token> and used to HMAC-SHA256 sign requests.
         */
        'auth_token' => env('STEADFAST_WEBHOOK_TOKEN', ''),

        /**
         * Verify incoming request signature (X-Signature) with the auth token.
         */
        'verify_signature' => (bool) env('STEADFAST_WEBHOOK_VERIFY_SIGNATURE', true),

        /**
         * Route middleware applied to the webhook endpoint.
         */
        'middleware' => ['api'],

        /**
         * Whether to log incoming webhook payloads to Laravel log.
         */
        'log_events' => (bool) env('STEADFAST_WEBHOOK_LOG', true),

        /**
         * Check and prevent processing duplicate requests using Idempotency-Key.
         */
        'handle_idempotency' => (bool) env('STEADFAST_WEBHOOK_IDEMPOTENCY', true),

        /**
         * Idempotency cache duration in seconds (default 24 hours).
         */
        'idempotency_cache_ttl' => (int) env('STEADFAST_WEBHOOK_IDEMPOTENCY_TTL', 86400),
    ],
];

