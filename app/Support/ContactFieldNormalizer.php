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

        // UX: users often paste plain domains with trailing punctuation from chat/apps,
        // e.g. "example.com," or "(example.com)".
        // Only do this for scheme-less "domain-like" inputs to avoid breaking valid URLs
        // where the path legitimately ends with punctuation (e.g. Wikipedia).
        if (! str_contains($v, '/')) {
            $v = trim($v, "\"'.,;:()[]{}<>");
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

        // UX/robustness: if after normalization there are no digits at all,
        // treat it as empty (e.g. user accidentally types only "+" or "-" etc.).
        if (preg_match('/\d/', $v) !== 1) {
            return null;
        }

        return $v;
    }
}
