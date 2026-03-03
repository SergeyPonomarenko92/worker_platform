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
    public function it_removes_invisible_separators_without_concatenating_words(): void
    {
        $input = "hello\u{200B}world\u{2060}again"; // zero-width space + word joiner

        $this->assertSame('hello world again', QueryParamNormalizer::text($input));
    }

    #[Test]
    public function it_returns_empty_string_for_null_or_whitespace_only(): void
    {
        $this->assertSame('', QueryParamNormalizer::text(null));
        $this->assertSame('', QueryParamNormalizer::text("\u{00A0}   \n\t"));
    }
}
