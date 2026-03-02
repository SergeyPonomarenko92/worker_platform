<?php

namespace Tests\Unit;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    #[DataProvider('websiteCases')]
    public function test_website_normalization(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::website($input));
    }

    #[DataProvider('phoneCases')]
    public function test_phone_normalization(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::phone($input));
    }

    public static function websiteCases(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'spaces only' => ['   ', null],
            'nbsp only' => ["\u{00A0}\u{202F}", null],

            'keeps http url' => ['http://example.com', 'http://example.com'],
            'keeps https url' => ['https://example.com', 'https://example.com'],
            'adds https scheme to bare host' => ['example.com', 'https://example.com'],
            'trims and collapses whitespace' => ["  example.com\t\n  ", 'https://example.com'],
            'normalizes nbsp in value' => ["example\u{00A0}.com", 'https://example .com'],
        ];
    }

    public static function phoneCases(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'spaces only' => ['   ', null],
            'nbsp only' => ["\u{00A0}\u{202F}", null],

            'keeps digits and plus' => ['+380 67 123 45 67', '+380 67 123 45 67'],
            'collapses whitespace' => [" +380\t67\n123\r\n4567 ", '+380 67 123 4567'],
        ];
    }
}
