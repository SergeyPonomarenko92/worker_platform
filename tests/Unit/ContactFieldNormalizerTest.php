<?php

namespace Tests\Unit;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    #[Test]
    public function website_returns_null_for_empty_input(): void
    {
        $this->assertNull(ContactFieldNormalizer::website(null));
        $this->assertNull(ContactFieldNormalizer::website(''));
        $this->assertNull(ContactFieldNormalizer::website("\u{00A0}   \n\t"));
    }

    #[Test]
    public function website_prefixes_https_for_plain_domains(): void
    {
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('example.com'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('   example.com   '));

        // Users often paste domains with trailing punctuation from chat/apps.
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('example.com,'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('(example.com)'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('"example.com"'));
    }

    #[Test]
    public function website_normalizes_protocol_relative_urls(): void
    {
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('//example.com'));
    }

    #[Test]
    public function website_preserves_http_and_https_urls(): void
    {
        $this->assertSame('http://example.com', ContactFieldNormalizer::website('http://example.com'));
        $this->assertSame('https://example.com', ContactFieldNormalizer::website('https://example.com'));

        // Robustness: do not change casing.
        $this->assertSame('HTTPS://EXAMPLE.COM', ContactFieldNormalizer::website('HTTPS://EXAMPLE.COM'));
    }

    #[Test]
    public function website_does_not_override_other_schemes(): void
    {
        $this->assertSame('mailto:test@example.com', ContactFieldNormalizer::website('mailto:test@example.com'));
        $this->assertSame('ftp://example.com', ContactFieldNormalizer::website('ftp://example.com'));
    }

    #[Test]
    public function website_does_not_strip_punctuation_when_a_path_is_present(): void
    {
        // If a path is present, do not strip trailing punctuation, because it can be meaningful.
        $this->assertSame('https://example.com/path,', ContactFieldNormalizer::website('https://example.com/path,'));

        // But for scheme-less inputs, we still prefix https.
        $this->assertSame('https://example.com/path,', ContactFieldNormalizer::website('example.com/path,'));
    }

    #[Test]
    public function phone_returns_null_for_empty_or_digitless_input(): void
    {
        $this->assertNull(ContactFieldNormalizer::phone(null));
        $this->assertNull(ContactFieldNormalizer::phone(''));
        $this->assertNull(ContactFieldNormalizer::phone("\u{00A0}   \n\t"));

        // No digits at all.
        $this->assertNull(ContactFieldNormalizer::phone('+'));
        $this->assertNull(ContactFieldNormalizer::phone('---'));
        $this->assertNull(ContactFieldNormalizer::phone(' ( ) '));
    }

    #[Test]
    public function phone_trims_and_collapses_whitespace_but_keeps_formatting_chars(): void
    {
        $this->assertSame('+380 50 123 45 67', ContactFieldNormalizer::phone("  +380\u{00A0}  50\n 123\t45  67  "));
    }
}
