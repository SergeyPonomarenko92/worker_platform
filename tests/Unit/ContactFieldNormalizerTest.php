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
            'spaces only' => [" \t\n ", null],
            'unicode spaces only' => ["\u{00A0}\u{202F}", null],

            'keeps https' => ['https://example.com', 'https://example.com'],
            'keeps http' => ['http://example.com', 'http://example.com'],
            'adds https when missing scheme' => ['example.com', 'https://example.com'],
            'normalizes whitespace' => ["  example.com\u{00A0}", 'https://example.com'],
            'does not downcase host (leave as-is)' => ['ExAmPlE.com', 'https://ExAmPlE.com'],
        ];
    }

    public static function phoneCases(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'spaces only' => ['   ', null],
            'unicode spaces only' => ["\u{00A0}\u{202F}", null],

            'keeps phone as-is except whitespace normalization' => ["  +38\u{00A0}067\u{202F}123 45 67  ", '+38 067 123 45 67'],
        ];
    }
}
