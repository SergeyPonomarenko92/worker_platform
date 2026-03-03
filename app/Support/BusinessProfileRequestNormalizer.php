<?php

namespace App\Support;

class BusinessProfileRequestNormalizer
{
    public static function nullableText(?string $raw): ?string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return null;
        }

        return $v;
    }

    public static function countryCode(?string $raw): string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return 'UA';
        }

        // Robustness: accept inputs like "u a", "UA!", "USA" etc.
        // Keep only ASCII letters and take the first two characters.
        $v = preg_replace('/[^A-Za-z]/', '', $v) ?? '';
        $v = strtoupper($v);

        if (strlen($v) < 2) {
            return 'UA';
        }

        return substr($v, 0, 2);
    }
}
