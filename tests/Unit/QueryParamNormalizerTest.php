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
            'empty' => ['', ''],
            'whitespace only' => ["   \n\t  ", ''],

            'plain slug' => ['demo-provider', 'demo-provider'],
            'plain slug uppercase' => ['DEMO-PROVIDER', 'demo-provider'],
            'slug with surrounding whitespace' => ['  demo-provider  ', 'demo-provider'],

            'slug with trailing slash' => ['demo-provider/', 'demo-provider'],
            'providers path' => ['/providers/demo-provider/', 'demo-provider'],

            'full url with query and fragment' => [
                'https://example.test/providers/DEMO-PROVIDER/?utm=1#offers',
                'demo-provider',
            ],

            'url without scheme (still contains /providers/)' => [
                'example.test/providers/DEMO-PROVIDER?ref=catalog',
                'demo-provider',
            ],

            'providers path with extra segments' => [
                '/providers/demo-provider/something-else',
                'demo-provider',
            ],
        ];
    }
}
