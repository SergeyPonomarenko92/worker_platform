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
            'null -> null' => [null, null],
            'empty string -> null' => ['', null],
            'spaces -> null' => ['   ', null],
            'nbsp -> null' => ["\u{00A0}", null],

            'keeps https' => ['https://example.com', 'https://example.com'],
            'keeps http' => ['http://example.com', 'http://example.com'],
            'adds https to bare domain' => ['example.com', 'https://example.com'],
            'adds https to domain with path' => ['example.com/path', 'https://example.com/path'],

            'trims and collapses whitespace' => ["  example.com\t /path  ", 'https://example.com /path'],
            'trims unicode spaces' => ["\u{00A0}example.com\u{202F}", 'https://example.com'],
        ];
    }

    public static function phoneCases(): array
    {
        return [
            'null -> null' => [null, null],
            'empty string -> null' => ['', null],
            'spaces -> null' => ['   ', null],
            'keeps digits' => ['+380991112233', '+380991112233'],
            'trims unicode spaces' => ["\u{00A0}+380 99 111 22 33\u{202F}", '+380 99 111 22 33'],
            'collapses whitespace' => ["  +380\t99\n111  22  33 ", '+380 99 111 22 33'],
        ];
    }
}
