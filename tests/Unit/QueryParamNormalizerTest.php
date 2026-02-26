<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    #[DataProvider('providerSlugCases')]
    public function test_provider_slug_normalization(?string $input, string $expected): void
    {
        $this->assertSame($expected, QueryParamNormalizer::providerSlug($input));
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
            'relative providers url trailing slash' => ['/providers/demo-provider/', 'demo-provider'],
            'relative providers url with extra segments' => ['/providers/demo-provider/offers', 'demo-provider'],

            'absolute providers url with query and hash' => ['https://example.test/providers/demo-provider?ref=catalog#offers', 'demo-provider'],
            'absolute providers url with trailing slash + hash' => ['https://example.test/providers/demo-provider/#offers', 'demo-provider'],

            'keeps internal whitespace out' => ["  /providers/DEMO-provider/  ", 'demo-provider'],
        ];
    }
}
