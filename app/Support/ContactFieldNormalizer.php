<?php

namespace App\Support;

class ContactFieldNormalizer
{
    public static function website(?string $raw): ?string
    {
        // `trim()` does not remove NBSP and some other unicode spaces.
        // For contact fields we want predictable whitespace normalization.
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $v) === 1) {
            return $v;
        }

        return 'https://'.$v;
    }

    public static function phone(?string $raw): ?string
    {
        // `trim()` does not remove NBSP and some other unicode spaces.
        // For phone numbers we want predictable whitespace normalization.
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return null;
        }

        return $v;
    }
}
