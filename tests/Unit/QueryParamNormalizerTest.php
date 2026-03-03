<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    #[Test]
    public function it_trims_and_collapses_whitespace(): void
    {
        $this->assertSame('hello world', QueryParamNormalizer::text("  hello\n\t  world  "));
    }

    #[Test]
    public function it_treats_non_breaking_and_thin_spaces_as_normal_spaces(): void
    {
        $input = "\u{00A0}hello\u{2009}world\u{202F}"; // NBSP + thin space + narrow no-break space

        $this->assertSame('hello world', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_treats_mongolian_vowel_separator_as_a_space(): void
    {
        $input = "hello\u{180E}world";

        $this->assertSame('hello world', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_removes_invisible_separators_without_concatenating_words(): void
    {
        $input = "hello\u{200B}world\u{2060}again"; // zero-width space + word joiner

        $this->assertSame('hello world again', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_normalizes_bidi_control_marks_as_spaces(): void
    {
        $input = "hello\u{200E}world\u{202E}again\u{2066}done"; // LRM + RLO + LRI

        $this->assertSame('hello world again done', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_returns_empty_string_for_null_or_whitespace_only(): void
    {
        $this->assertSame('', QueryParamNormalizer::text(null));
        $this->assertSame('', QueryParamNormalizer::text("\u{00A0}   \n\t"));
    }

    #[Test]
    public function it_normalizes_provider_slug_from_plain_or_url_inputs(): void
    {
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('/providers/demo-provider/'));

        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/providers/DEMO-provider?ref=catalog#offers')
        );

        // When users paste a catalog URL with provider in the query string,
        // extract and normalize the actual provider value.
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/catalog?provider=demo-provider')
        );

        // Provider value itself might be a pasted provider URL (and could be percent-encoded).
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/catalog?provider=https%3A%2F%2Fexample.test%2Fproviders%2Fdemo-provider%2F')
        );

        // Be robust to extra punctuation/quotes around a pasted slug.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('("demo-provider"),'));

        // Some apps include an @ prefix (e.g. "@demo-provider") when sharing handles.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('@demo-provider'));

        // Support percent-encoded values in the URL path.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('/providers/demo%2Dprovider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('https://example.test/providers/demo%2Dprovider'));

        // Support percent-encoded values pasted into query string.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('https://example.test/catalog?provider=demo%2Dprovider'));
    }

    #[Test]
    public function it_returns_empty_provider_slug_for_null_or_whitespace_only(): void
    {
        $this->assertSame('', QueryParamNormalizer::providerSlug(null));
        $this->assertSame('', QueryParamNormalizer::providerSlug(''));
        $this->assertSame('', QueryParamNormalizer::providerSlug("\u{00A0}   \n\t"));
    }
}
