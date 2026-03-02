<?php

namespace Tests\Unit;

use App\Support\SqlLikeEscaper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SqlLikeEscaperTest extends TestCase
{
    #[DataProvider('escapeCases')]
    public function test_escape(string $input, string $expected): void
    {
        $this->assertSame($expected, SqlLikeEscaper::escape($input));
    }

    public static function escapeCases(): array
    {
        return [
            'empty' => ['', ''],
            'no special chars' => ['kyiv', 'kyiv'],
            'escapes percent' => ['100% legit', '100!% legit'],
            'escapes underscore' => ['a_b', 'a!_b'],
            'escapes escape char itself first' => ['a!b', 'a!!b'],
            'escapes combination' => ['!%_', '!!!%!_'],
        ];
    }
}
