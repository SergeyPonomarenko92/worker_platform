<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    public static function providerSlugCases(): array
    {
        return [
            'empty' => [null, ''],
            'blank' => ['   ', ''],
            'plain slug' => ['demo-provider', 'demo-provider'],
            'slug with trailing slash' => ['demo-provider/', 'demo-provider'],
            'path only' => ['/providers/demo-provider/', 'demo-provider'],
            'full url with query+hash' => ['https://example.test/providers/demo-provider?ref=catalog#offers', 'demo-provider'],
            'spaces around' => ['  Demo-Provider  ', 'demo-provider'],
            'uppercase ukrainian' => ['ПРОВАЙДЕР', 'провайдер'],
            'url with extra segments' => ['https://example.test/providers/demo-provider/offers', 'demo-provider'],
            'url with whitespace' => ["  https://example.test/providers/demo-provider  ", 'demo-provider'],
            'non providers url falls back to trimming' => ['https://example.test/something/demo-provider', 'https://example.test/something/demo-provider'],
            'double slashes trimmed' => ['//demo-provider//', 'demo-provider'],
        ];
    }

    #[DataProvider('providerSlugCases')]
    public function test_provider_slug_normalization($input, string $expected): void
    {
        $this->assertSame($expected, QueryParamNormalizer::providerSlug($input));
    }
}
