<?php

use AhRaihan\SteadfastCourier\Http\Controllers\WebhookController;
use AhRaihan\SteadfastCourier\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;

$webhookPath = config('steadfast.webhook.url_path', 'steadfast/webhook');
$middleware = (array) config('steadfast.webhook.middleware', ['api']);

Route::post($webhookPath, [WebhookController::class, 'handle'])
    ->middleware(array_merge($middleware, [VerifyWebhookSignature::class]))
    ->name('steadfast.webhook');

