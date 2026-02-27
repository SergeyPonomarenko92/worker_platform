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
            'nbspace-only becomes null' => ["\u{00A0}\u{202F}", null],
            'adds https when missing scheme' => ['example.com', 'https://example.com'],
            'normalizes unicode spaces around value' => ["\u{00A0}example.com\u{202F}", 'https://example.com'],
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
            'nbspace-only becomes null' => ["\u{00A0}\u{202F}", null],
            'trims' => ['  +380 99 123 45 67  ', '+380 99 123 45 67'],
            'normalizes unicode spaces' => ["\u{00A0}+380\u{202F}99\u{00A0}123\u{202F}45\u{00A0}67\u{00A0}", '+380 99 123 45 67'],
        ];
    }

    #[DataProvider('phoneCases')]
    public function test_phone_normalization(?string $raw, ?string $expected): void
    {
        $this->assertSame($expected, ContactFieldNormalizer::phone($raw));
    }
}
