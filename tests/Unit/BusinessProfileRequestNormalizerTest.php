<?php

namespace Tests\Unit;

use App\Support\BusinessProfileRequestNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessProfileRequestNormalizerTest extends TestCase
{
    #[Test]
    public function it_converts_nullable_text_inputs_to_null_when_empty_after_normalization(): void
    {
        $this->assertNull(BusinessProfileRequestNormalizer::nullableText(null));
        $this->assertNull(BusinessProfileRequestNormalizer::nullableText(''));
        $this->assertNull(BusinessProfileRequestNormalizer::nullableText("\u{00A0}  \n\t"));

        $this->assertSame('hello world', BusinessProfileRequestNormalizer::nullableText("  hello\n\t  world  "));
    }

    #[Test]
    public function it_normalizes_country_code_with_sane_defaults(): void
    {
        // Default when empty
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode(null));
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode(''));
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode(" \u{00A0}\n\t "));

        // Robustness for weird user input
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode('u a'));
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode('ua!'));

        // Extra letters should be ignored after the first 2
        $this->assertSame('US', BusinessProfileRequestNormalizer::countryCode('USA'));

        // Any longer input should still collapse to 2 letters.
        $this->assertSame('FR', BusinessProfileRequestNormalizer::countryCode('FRANCE'));

        // Mixed punctuation and casing
        $this->assertSame('PL', BusinessProfileRequestNormalizer::countryCode('p-l'));
        $this->assertSame('DE', BusinessProfileRequestNormalizer::countryCode('De'));

        // If after stripping there are less than 2 letters, fallback to UA
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode('U'));
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode('1!'));
    }

    #[Test]
    public function it_normalizes_country_code_with_invisible_unicode_chars(): void
    {
        // BOM + zero-width space wrapping normal input.
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode("\u{FEFF}UA\u{200B}"));

        // Left-to-right mark between letters (can appear in RTL-context copy/paste).
        $this->assertSame('PL', BusinessProfileRequestNormalizer::countryCode("P\u{200E}L"));

        // Soft hyphen inside letters — stripped by text(), then letters join.
        $this->assertSame('DE', BusinessProfileRequestNormalizer::countryCode("D\u{00AD}E"));

        // Only invisible chars → fallback to UA.
        $this->assertSame('UA', BusinessProfileRequestNormalizer::countryCode("\u{200B}\u{FEFF}\u{200E}"));
    }
}
