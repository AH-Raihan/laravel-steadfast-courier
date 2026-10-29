<?php

namespace AhRaihan\SteadfastCourier\Tests\Unit;

use AhRaihan\SteadfastCourier\Helpers\Sanitizer;
use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{
    public function test_it_strips_restricted_characters(): void
    {
        $input = 'House {12}, Road <3>; Dhaka $city';
        $cleaned = Sanitizer::strip($input);

        $this->assertEquals('House  12 , Road  3   Dhaka  city', $cleaned);
        $this->assertStringNotContainsString('{', $cleaned);
        $this->assertStringNotContainsString('}', $cleaned);
        $this->assertStringNotContainsString(';', $cleaned);
        $this->assertStringNotContainsString('<', $cleaned);
        $this->assertStringNotContainsString('>', $cleaned);
        $this->assertStringNotContainsString('$', $cleaned);
    }

    public function test_it_formats_bangladeshi_phone(): void
    {
        $this->assertEquals('01712345678', Sanitizer::formatPhone('+8801712345678'));
        $this->assertEquals('01712345678', Sanitizer::formatPhone('8801712345678'));
        $this->assertEquals('01712345678', Sanitizer::formatPhone('01712345678'));
        $this->assertEquals('01812345678', Sanitizer::formatPhone('01812-345678'));
    }

    public function test_it_formats_invoice(): void
    {
        $invoice = 'INV#10231@2026/09';
        $cleaned = Sanitizer::formatInvoice($invoice);

        $this->assertEquals('INV10231202609', $cleaned);
        $this->assertEquals('ORD-10231_B', Sanitizer::formatInvoice('ORD-10231_B'));
    }

    public function test_it_sanitizes_full_order_payload(): void
    {
        $payload = [
            'invoice' => 'INV-9999!',
            'recipient_name' => 'John <Doe>',
            'recipient_phone' => '+8801711223344',
            'recipient_address' => 'House $10; Sector 4',
            'cod_amount' => '1500.50',
            'note' => 'Call {before} 3pm',
        ];

        $sanitized = Sanitizer::sanitizeOrderPayload($payload);

        $this->assertEquals('INV-9999', $sanitized['invoice']);
        $this->assertEquals('John  Doe', $sanitized['recipient_name']);
        $this->assertEquals('01711223344', $sanitized['recipient_phone']);
        $this->assertEquals('House  10  Sector 4', $sanitized['recipient_address']);
        $this->assertEquals(1500.50, $sanitized['cod_amount']);
        $this->assertEquals('Call  before  3pm', $sanitized['note']);
    }
}
