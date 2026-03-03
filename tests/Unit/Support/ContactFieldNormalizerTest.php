<?php

namespace Tests\Unit\Support;

use App\Support\ContactFieldNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactFieldNormalizerTest extends TestCase
{
    #[Test]
    public function phone_returns_null_for_null_and_empty_and_whitespace(): void
    {
        $this->assertNull(ContactFieldNormalizer::phone(null));
        $this->assertNull(ContactFieldNormalizer::phone(''));
        $this->assertNull(ContactFieldNormalizer::phone('   '));

        // NBSP should be treated as whitespace.
        $this->assertNull(ContactFieldNormalizer::phone("\u{00A0}"));
        $this->assertNull(ContactFieldNormalizer::phone("\u{00A0} \u{00A0}"));
    }

    #[Test]
    public function phone_returns_null_if_no_digits_after_normalization(): void
    {
        $this->assertNull(ContactFieldNormalizer::phone('+'));
        $this->assertNull(ContactFieldNormalizer::phone('-'));
        $this->assertNull(ContactFieldNormalizer::phone('---'));
        $this->assertNull(ContactFieldNormalizer::phone('+ - ( )'));

        // NBSP + punctuation only.
        $this->assertNull(ContactFieldNormalizer::phone("\u{00A0}+( )\u{00A0}"));
    }

    #[Test]
    public function phone_trims_and_collapses_whitespace_but_keeps_user_formatting(): void
    {
        $this->assertSame('+380 67 123 45 67', ContactFieldNormalizer::phone('  +380  67   123  45  67  '));

        // NBSP and mixed whitespace should normalize to single spaces.
        $raw = "\u{00A0}+380\u{00A0}67\n123\t45\u{00A0}67\u{00A0}";
        $this->assertSame('+380 67 123 45 67', ContactFieldNormalizer::phone($raw));
    }
}
