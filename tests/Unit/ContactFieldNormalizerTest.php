<?php

namespace Tests\Unit;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    public static function phoneProvider(): array
    {
        return [
            'null stays null' => [null, null],
            'empty string becomes null' => ['', null],
            'spaces become null' => ['   ', null],
            'nbsp becomes null' => ["\u{00A0}", null],
            'only plus treated as empty' => ['+', null],
            'only punctuation treated as empty' => ['--', null],
            'keeps digits with surrounding whitespace' => ['  +380 50 123 45 67  ', '+380 50 123 45 67'],
            'collapses unicode and multiple spaces inside the value' => ["+380\u{00A0}  50   123\u{00A0}45  67", '+380 50 123 45 67'],
            'keeps formatting characters as-is (no aggressive sanitization)' => ['(050) 123-45-67', '(050) 123-45-67'],
        ];
    }

    #[DataProvider('phoneProvider')]
    public function test_phone_normalization(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::phone($input));
    }

    public static function websiteProvider(): array
    {
        return [
            'null stays null' => [null, null],
            'empty string becomes null' => ['', null],
            'spaces become null' => ['   ', null],
            'nbsp becomes null' => ["\u{00A0}", null],
            'protocol-relative gets https' => ['//example.com', 'https://example.com'],
            'already https stays as-is' => ['https://example.com', 'https://example.com'],
            'already http stays as-is' => ['http://example.com', 'http://example.com'],
            'http/https scheme is case-insensitive' => ['HTTP://example.com', 'HTTP://example.com'],
            'no scheme gets https prefix' => ['example.com', 'https://example.com'],
            'trims and collapses whitespace' => ["  example.com\u{00A0}", 'https://example.com'],
            'other scheme is preserved (validation should decide)' => ['mailto:test@example.com', 'mailto:test@example.com'],
        ];
    }

    #[DataProvider('websiteProvider')]
    public function test_website_normalization(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::website($input));
    }
}
