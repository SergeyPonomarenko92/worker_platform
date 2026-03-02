<?php

namespace Tests\Unit;

use App\Support\SqlLikeEscaper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SqlLikeEscaperTest extends TestCase
{
    #[Test]
    public function it_escapes_percent_and_underscore_for_like_queries(): void
    {
        $this->assertSame('100!% done', SqlLikeEscaper::escape('100% done'));
        $this->assertSame('file!_name', SqlLikeEscaper::escape('file_name'));
        $this->assertSame('a!%b!_c', SqlLikeEscaper::escape('a%b_c'));
    }

    #[Test]
    public function it_escapes_the_escape_character_itself_first(): void
    {
        // When the input contains the escape char, it should be doubled.
        $this->assertSame('wow!!100!%', SqlLikeEscaper::escape('wow!100%'));
    }

    #[Test]
    public function it_supports_custom_escape_character(): void
    {
        $this->assertSame('100\\% and a\\_b', SqlLikeEscaper::escape('100% and a_b', '\\'));
        $this->assertSame('x\\\\y', SqlLikeEscaper::escape('x\\y', '\\'));
    }
}
