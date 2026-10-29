<?php

namespace AhRaihan\SteadfastCourier\Tests;

use AhRaihan\SteadfastCourier\SteadfastServiceProvider;

if (class_exists('Orchestra\Testbench\TestCase')) {
    abstract class BaseTestCase extends \Orchestra\Testbench\TestCase {}
} elseif (class_exists('\Tests\TestCase')) {
    abstract class BaseTestCase extends \Tests\TestCase {}
} else {
    abstract class BaseTestCase extends \Illuminate\Foundation\Testing\TestCase {}
}

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SteadfastServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('steadfast.api_key', 'test_api_key_123');
        config()->set('steadfast.secret_key', 'test_secret_key_456');
        config()->set('steadfast.base_url', 'https://portal.packzy.com/api/v1');
        config()->set('steadfast.webhook.auth_token', 'test_webhook_token_789');
        config()->set('steadfast.webhook.verify_signature', true);
        config()->set('steadfast.webhook.handle_idempotency', true);
    }
}
