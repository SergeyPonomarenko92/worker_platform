<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    #[Test]
    public function it_returns_empty_string_for_null_or_empty_input(): void
    {
        $this->assertSame('', QueryParamNormalizer::providerSlug(null));
        $this->assertSame('', QueryParamNormalizer::providerSlug(''));
        $this->assertSame('', QueryParamNormalizer::providerSlug("   \n\t  "));
    }

    #[Test]
    public function it_normalizes_plain_slug_and_trims_slashes_and_whitespace(): void
    {
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('  demo-provider  '));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('/demo-provider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug("\n\tDEMO-PROVIDER\t\n"));
    }

    #[Test]
    public function it_can_extract_slug_from_provider_url_with_query_or_fragment(): void
    {
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/providers/DEMO-PROVIDER?ref=catalog')
        );

        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/providers/demo-provider/#portfolio')
        );

        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('/providers/demo-provider/?ref=catalog#offers')
        );
    }
}
