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
            'nbps only' => ["\u{00A0}\u{00A0}", null],

            'adds https:// when missing' => ['example.com', 'https://example.com'],
            'keeps https://' => ['https://example.com', 'https://example.com'],
            'keeps http://' => ['http://example.com', 'http://example.com'],
            'normalizes protocol-relative url' => ['//example.com', 'https://example.com'],

            'trims and collapses whitespace' => ["  example.com\n ", 'https://example.com'],
            'normalizes nbsp around' => ["example.com\u{00A0}", 'https://example.com'],
        ];
    }

    public static function phoneCases(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'spaces only' => ['   ', null],
            'nbps only' => ["\u{00A0}\u{00A0}", null],

            'keeps phone as-is (after normalization)' => ['+380 50 123 45 67', '+380 50 123 45 67'],
            'trims and collapses whitespace' => ["  +380\t50\n123  ", '+380 50 123'],
        ];
    }
}
