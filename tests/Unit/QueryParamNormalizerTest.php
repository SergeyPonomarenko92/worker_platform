<?php

namespace Tests\Unit;

use App\Support\QueryParamNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryParamNormalizerTest extends TestCase
{
    #[Test]
    public function it_normalizes_unsigned_int_from_formatted_numbers(): void
    {
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt('1000'));
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt('1 000'));
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("1\u{00A0}000"));
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("1\u{202F}000"));
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("1'000"));
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt('1’000'));

        $this->assertNull(QueryParamNormalizer::unsignedInt(null));
        $this->assertNull(QueryParamNormalizer::unsignedInt(''));
        $this->assertNull(QueryParamNormalizer::unsignedInt("\u{00A0}  \n\t"));
        $this->assertNull(QueryParamNormalizer::unsignedInt('abc'));
        $this->assertNull(QueryParamNormalizer::unsignedInt('10-00'));
        $this->assertNull(QueryParamNormalizer::unsignedInt('-1'));
    }

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
    public function it_strips_soft_hyphen_inside_words(): void
    {
        $input = "pro\u{00AD}vider"; // soft hyphen from copy/paste

        $this->assertSame('provider', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_normalizes_bidi_control_marks_as_spaces(): void
    {
        $input = "hello\u{200E}world\u{202E}again\u{2066}done"; // LRM + RLO + LRI

        $this->assertSame('hello world again done', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_treats_zero_width_and_bom_marks_as_spaces(): void
    {
        $input = "he\u{200C}llo\u{FEFF}world"; // ZWNJ + BOM

        $this->assertSame('he llo world', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_returns_empty_string_for_null_or_whitespace_only(): void
    {
        $this->assertSame('', QueryParamNormalizer::text(null));
        $this->assertSame('', QueryParamNormalizer::text("\u{00A0}   \n\t"));
    }

    #[Test]
    public function it_normalizes_emails_from_common_copy_paste_formats(): void
    {
        $this->assertSame('user@example.com', QueryParamNormalizer::email('user@example.com'));
        $this->assertSame('user@example.com', QueryParamNormalizer::email('USER@EXAMPLE.COM'));

        $this->assertSame('user@example.com', QueryParamNormalizer::email('User Name <user@example.com>'));
        $this->assertSame('user@example.com', QueryParamNormalizer::email('mailto:user@example.com'));

        // Be robust to unicode whitespace/invisible chars around pasted values.
        $this->assertSame('user@example.com', QueryParamNormalizer::email("\u{00A0}user@example.com\u{200B}"));

        // When nothing usable found: empty string.
        $this->assertSame('', QueryParamNormalizer::email(null));
        $this->assertSame('', QueryParamNormalizer::email("\u{00A0}  \n\t"));
    }

    #[Test]
    public function it_normalizes_provider_slug_from_plain_or_url_inputs(): void
    {
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('demo-provider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('/providers/demo-provider/'));

        // Be robust to invisible/unicode whitespace around pasted values.
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug("\u{00A0} https://example.test/providers/demo-provider/ \u{200B}")
        );

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

        // Extract provider even when catalog URL has extra params/fragments.
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/catalog?sort=newest&provider=demo-provider#top')
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

        // Support relative paths without leading slash.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('providers/demo-provider'));

        // Support different casing in the providers segment.
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('/Providers/demo-provider/'));
        $this->assertSame('demo-provider', QueryParamNormalizer::providerSlug('https://example.test/Providers/demo-provider/'));

        // Percent-encoded provider values in query string should be decoded,
        // then normalized the same way as plain input.
        $this->assertSame(
            'demo-provider',
            QueryParamNormalizer::providerSlug('https://example.test/catalog?provider=%40Demo-provider')
        );
    }

    #[Test]
    public function it_normalizes_unsigned_int_with_invisible_unicode_around_digits(): void
    {
        // BOM + digits + zero-width space: should be treated as plain "1000".
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("\u{FEFF}1000\u{200B}"));

        // Zero-width non-joiner inside formatted number.
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("1\u{200C}000"));

        // Left-to-right mark (common in pasted content from RTL-aware editors).
        $this->assertSame(500, QueryParamNormalizer::unsignedInt("\u{200E}500"));

        // Soft hyphen inside digits — stripped entirely by text(), remaining digits join.
        $this->assertSame(1000, QueryParamNormalizer::unsignedInt("10\u{00AD}00"));

        // Only invisible chars → null.
        $this->assertNull(QueryParamNormalizer::unsignedInt("\u{FEFF}\u{200B}\u{200E}"));
    }

    #[Test]
    public function it_returns_empty_provider_slug_for_null_or_whitespace_only(): void
    {
        $this->assertSame('', QueryParamNormalizer::providerSlug(null));
        $this->assertSame('', QueryParamNormalizer::providerSlug(''));
        $this->assertSame('', QueryParamNormalizer::providerSlug("\u{00A0}   \n\t"));
    }
}
