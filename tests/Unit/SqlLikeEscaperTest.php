<?php

namespace Tests\Unit;

use App\Support\SqlLikeEscaper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SqlLikeEscaperTest extends TestCase
{
    #[DataProvider('escapeCases')]
    public function test_escape(string $input, string $expected, string $escapeChar = '!'): void
    {
        $this->assertSame($expected, SqlLikeEscaper::escape($input, $escapeChar));
    }

    public static function escapeCases(): array
    {
        return [
            'no wildcards' => ['abc', 'abc'],
            'escapes percent' => ['100% legit', '100!% legit'],
            'escapes underscore' => ['a_b', 'a!_b'],
            'escapes both' => ['%_%', '!%!_!%'],

            'escapes escape-char itself first (double it)' => ['a!b', 'a!!b'],
            'escape-char mixed with wildcards' => ['!%_', '!!!%!_'],

            'custom escape char' => ['a#b%_#', 'a##b#%#_##', '#'],
        ];
    }
}
