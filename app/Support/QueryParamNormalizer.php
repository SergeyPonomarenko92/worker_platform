<?php

namespace App\Support;

class QueryParamNormalizer
{
    /**
     * Normalize a free-form text query param.
     *
     * - trims
     * - collapses whitespace
     * - removes/normalizes invisible unicode separators that may appear on copy/paste
     */
    public static function text(?string $input): string
    {
        $value = (string) $input;

        // Normalize common "non-breaking" and thin spaces that often appear when users copy/paste.
        // Do it BEFORE trim(), because PHP's trim() does not remove NBSP.
        // - NBSP (U+00A0)
        // - Figure space (U+2007)
        // - Thin space (U+2009)
        // - Hair space (U+200A)
        // - Narrow no-break space (U+202F)
        $value = str_replace([
            "\u{00A0}",
            "\u{2007}",
            "\u{2009}",
            "\u{200A}",
            "\u{202F}",
        ], ' ', $value);

        // Normalize invisible separators that are not matched by trim() and can break search.
        // Treat them as spaces to avoid accidentally concatenating words.
        // - Zero width space (U+200B)
        // - Word joiner (U+2060)
        // - Zero width no-break space / BOM (U+FEFF)
        $value = str_replace([
            "\u{200B}",
            "\u{2060}",
            "\u{FEFF}",
        ], ' ', $value);

        $value = trim($value);

        if ($value === '') {
            return '';
        }

        // Collapse all whitespace (tabs/newlines/multiple spaces) into a single space.
        $value = (string) preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    /**
     * Normalize a provider slug passed via query string.
     *
     * Accepts:
     * - "demo-provider"
     * - "demo-provider/"
     * - "/providers/demo-provider/"
     * - "https://example.test/providers/demo-provider?ref=catalog#offers"
     */
    public static function providerSlug(?string $input): string
    {
        $providerInput = self::text($input);

        if ($providerInput === '') {
            return '';
        }

        $providerSlug = $providerInput;

        // Provider filter can be either a slug ("demo-provider") or a pasted provider URL.
        // Be robust to different casing in the path ("/Providers/...") when users copy/paste.
        // Also handle values without a leading slash ("providers/demo-provider") which can appear
        // when users copy relative URLs from some apps.
        if (preg_match('~(^|/)providers/~i', $providerSlug) === 1) {
            $path = parse_url($providerSlug, PHP_URL_PATH) ?: $providerSlug;

            $parts = preg_split('~providers/~i', (string) $path, 2);
            $after = $parts[1] ?? '';

            $providerSlug = trim(explode('/', ltrim($after, '/'), 2)[0] ?? '');
        }

        // Allow pasted values like "demo-provider/" or "/providers/demo-provider/".
        $providerSlug = trim($providerSlug, '/');

        return mb_strtolower($providerSlug, 'UTF-8');
    }
}
