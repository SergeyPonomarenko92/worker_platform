<?php

namespace Tests\Unit;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    public static function websiteCases(): array
    {
        return [
            'null stays null' => [null, null],
            'empty becomes null' => ['   ', null],
            'adds https when missing scheme' => ['example.com', 'https://example.com'],
            'keeps http scheme case' => [' HTTP://foo.test ', 'HTTP://foo.test'],
            'keeps https scheme' => ['https://bar.test', 'https://bar.test'],
        ];
    }

    #[DataProvider('websiteCases')]
    public function test_website_normalization(?string $raw, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::website($raw));
    }

    public static function phoneCases(): array
    {
        return [
            'null stays null' => [null, null],
            'empty becomes null' => ['  ', null],
            'trims' => ['  +380 99 123 45 67  ', '+380 99 123 45 67'],
        ];
    }

    #[DataProvider('phoneCases')]
    public function test_phone_normalization(?string $raw, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::phone($raw));
    }
}
