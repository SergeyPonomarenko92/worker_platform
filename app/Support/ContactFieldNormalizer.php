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

        // Some users paste protocol-relative URLs like "//example.com".
        // We store normalized http(s) URLs only.
        if (str_starts_with($v, '//')) {
            return 'https:'.$v;
        }

        if (preg_match('#^https?://#i', $v) === 1) {
            return $v;
        }

        // If user already provided some other scheme (ftp:, mailto:, javascript:, etc.)
        // do not try to "fix" it by prefixing https:// — validation should reject it.
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $v) === 1) {
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
