<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    #[DataProvider('textCases')]
    public function test_text_normalization(?string $input, string $expected): void
    {
        $this->assertSame($expected, QueryParamNormalizer::text($input));
    }

    #[DataProvider('providerSlugCases')]
    public function test_provider_slug_normalization(?string $input, string $expected): void
    {
        $this->assertSame($expected, QueryParamNormalizer::providerSlug($input));
    }

    public static function textCases(): array
    {
        return [
            'null' => [null, ''],
            'empty string' => ['', ''],
            'spaces only' => ['   ', ''],
            'keeps single spaces' => ['a b', 'a b'],
            'collapses whitespace' => ["  a\t b\n c  ", 'a b c'],
            'collapses nbsp and narrow nbsp' => ["a\u{00A0}\u{202F}b", 'a b'],
            'collapses thin spaces' => ["a\u{2007}\u{2009}\u{200A}b", 'a b'],
        ];
    }

    public static function providerSlugCases(): array
    {
        return [
            'null' => [null, ''],
            'empty string' => ['', ''],
            'spaces' => ['   ', ''],

            'plain slug' => ['demo-provider', 'demo-provider'],
            'plain slug uppercase' => ['Demo-Provider', 'demo-provider'],
            'slug with trailing slash' => ['demo-provider/', 'demo-provider'],
            'slug wrapped in slashes' => ['/demo-provider/', 'demo-provider'],

            'relative providers url' => ['/providers/demo-provider', 'demo-provider'],
            'relative providers url (case-insensitive)' => ['/Providers/Demo-provider', 'demo-provider'],
            'relative providers url without leading slash' => ['providers/demo-provider', 'demo-provider'],
            'relative providers url trailing slash' => ['/providers/demo-provider/', 'demo-provider'],
            'relative providers url with extra segments' => ['/providers/demo-provider/offers', 'demo-provider'],

            'absolute providers url with query and hash' => ['https://example.test/providers/demo-provider?ref=catalog#offers', 'demo-provider'],
            'absolute providers url with trailing slash + hash' => ['https://example.test/providers/demo-provider/#offers', 'demo-provider'],

            'keeps internal whitespace out' => ["  /providers/DEMO-provider/  ", 'demo-provider'],
        ];
    }
}
