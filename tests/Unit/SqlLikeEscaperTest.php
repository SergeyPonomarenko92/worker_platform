<?php

namespace Tests\Unit;

use App\Support\SqlLikeEscaper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SqlLikeEscaperTest extends TestCase
{
    #[DataProvider('cases')]
    public function test_escape(string $input, string $expected): void
    {
        $this->assertSame($expected, SqlLikeEscaper::escape($input));
    }

    public static function cases(): array
    {
        return [
            'no special chars' => ['kyiv', 'kyiv'],
            'percent' => ['100% legit', '100!% legit'],
            'underscore' => ['a_b', 'a!_b'],
            'escape char itself' => ['a!b', 'a!!b'],
            'combo' => ['!%_', '!!!%!_'],
        ];
    }
}
