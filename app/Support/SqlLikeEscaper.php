<?php

namespace App\Support;

class SqlLikeEscaper
{
    /**
     * Escapes user input for (I)LIKE queries so that % and _ are treated literally.
     *
     * Example usage:
     *  - prefix:  whereRaw("lower(city) like ? escape '!'", [SqlLikeEscaper::escape($cityLower)."%"])
     *  - contains: whereRaw("title ilike ? escape '!'", ['%'.SqlLikeEscaper::escape($q).'%'])
     */
    public static function escape(string $value, string $escapeChar = '!'): string
    {
        // Escape order matters: escape the escape-char itself first.
        return str_replace(
            [$escapeChar, '%', '_'],
            [$escapeChar.$escapeChar, $escapeChar.'%', $escapeChar.'_'],
            $value
        );
    }
}
