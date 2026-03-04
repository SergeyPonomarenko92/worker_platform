<?php

namespace Tests\Unit;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    #[Test]
    public function website_returns_null_for_empty_or_whitespace_only(): void
    {
        $this->assertNull(ContactFieldNormalizer::website(null));
        $this->assertNull(ContactFieldNormalizer::website(''));
        $this->assertNull(ContactFieldNormalizer::website("\u{00A0}  \n\t"));
    }

    #[Test]
    public function website_keeps_http_and_https_urls_as_is(): void
    {
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('https://example.com'));
        $this->assertSame('http://example.com/path?x=1#top', ContactFieldNormalizer::website('http://example.com/path?x=1#top'));

        // Preserve casing and do not attempt to reformat valid inputs.
        $this->assertSame('HTTPS://EXAMPLE.COM', ContactFieldNormalizer::website('HTTPS://EXAMPLE.COM'));
    }

    #[Test]
    public function website_converts_protocol_relative_urls_to_https(): void
    {
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('//example.com'));
        $this->assertSame('https://example.com/path', ContactFieldNormalizer::website('//example.com/path'));
    }

    #[Test]
    public function website_converts_protocol_relative_urls_even_with_unicode_whitespace_around(): void
    {
        $this->assertSame(
            'https://example.com',
            ContactFieldNormalizer::website("\u{00A0}  //example.com\u{202F} ")
        );

        $this->assertSame(
            'https://example.com/path',
            ContactFieldNormalizer::website("\n\t//example.com/path\u{00A0}")
        );
    }

    #[Test]
    public function website_prefixes_plain_domains_with_https_and_trims_trailing_punctuation(): void
    {
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('example.com'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website(' example.com '));

        // UX: pasted from chat with punctuation.
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('example.com,'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('(example.com)'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('"example.com"'));
    }

    #[Test]
    public function website_does_not_trim_punctuation_when_there_is_a_path(): void
    {
        // When user provides a path, we should not strip punctuation that could be meaningful.
        $this->assertSame('https://example.com/wiki/Hello,_World', ContactFieldNormalizer::website('example.com/wiki/Hello,_World'));
        $this->assertSame('https://example.com/abc.', ContactFieldNormalizer::website('example.com/abc.'));
    }

    #[Test]
    public function website_does_not_force_https_for_other_schemes(): void
    {
        // These should be rejected by validation elsewhere, but normalizer must not mutate them.
        $this->assertSame('mailto:test@example.com', ContactFieldNormalizer::website('mailto:test@example.com'));
        $this->assertSame('ftp://example.com', ContactFieldNormalizer::website('ftp://example.com'));
        $this->assertSame('javascript:alert(1)', ContactFieldNormalizer::website('javascript:alert(1)'));
    }

    #[Test]
    public function phone_returns_null_for_empty_or_non_digit_inputs(): void
    {
        $this->assertNull(ContactFieldNormalizer::phone(null));
        $this->assertNull(ContactFieldNormalizer::phone(''));
        $this->assertNull(ContactFieldNormalizer::phone("\u{00A0}  \n\t"));

        $this->assertNull(ContactFieldNormalizer::phone('+'));
        $this->assertNull(ContactFieldNormalizer::phone('---'));
        $this->assertNull(ContactFieldNormalizer::phone('( )'));
    }

    #[Test]
    public function phone_keeps_normalized_value_when_it_contains_digits(): void
    {
        $this->assertSame('+380 50 123 45 67', ContactFieldNormalizer::phone(" +380 50
	123   45 67 "));
    }
}
