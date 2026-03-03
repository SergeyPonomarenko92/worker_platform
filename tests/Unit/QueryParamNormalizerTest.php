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
            'collapses other unicode spaces' => ["a\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2008}\u{205F}\u{3000}b", 'a b'],
            'collapses zero width separators' => ["a\u{200B}\u{200C}\u{200D}\u{2060}\u{FEFF}b", 'a b'],
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
            'slug with query string' => ['demo-provider?ref=catalog', 'demo-provider'],
            'slug with hash' => ['demo-provider#offers', 'demo-provider'],

            'relative providers url' => ['/providers/demo-provider', 'demo-provider'],
            'relative providers url (case-insensitive)' => ['/Providers/Demo-provider', 'demo-provider'],
            'relative providers url without leading slash' => ['providers/demo-provider', 'demo-provider'],
            'relative providers url trailing slash' => ['/providers/demo-provider/', 'demo-provider'],
            'relative providers url with extra segments' => ['/providers/demo-provider/offers', 'demo-provider'],

            'absolute providers url with query and hash' => ['https://example.test/providers/demo-provider?ref=catalog#offers', 'demo-provider'],
            'absolute providers url with trailing slash + hash' => ['https://example.test/providers/demo-provider/#offers', 'demo-provider'],

            'catalog url with provider query param' => ['https://example.test/catalog?provider=demo-provider', 'demo-provider'],
            'catalog url with provider query param (provider is providers url)' => ['https://example.test/catalog?provider=/providers/demo-provider', 'demo-provider'],
            'relative catalog url with provider query param' => ['/catalog?provider=demo-provider', 'demo-provider'],

            'keeps internal whitespace out' => ["  /providers/DEMO-provider/  ", 'demo-provider'],

            'slug wrapped in parentheses' => ['(demo-provider)', 'demo-provider'],
            'slug wrapped in quotes' => ['"demo-provider"', 'demo-provider'],
            'slug with trailing punctuation' => ['demo-provider,', 'demo-provider'],
            'providers url wrapped in punctuation' => ['(https://example.test/providers/demo-provider/)', 'demo-provider'],
        ];
    }
}
