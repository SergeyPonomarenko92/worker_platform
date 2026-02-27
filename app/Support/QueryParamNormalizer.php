<?php

namespace App\Support;

class QueryParamNormalizer
{
    /**
     * Normalize a free-form text query param.
     *
     * - trims
     * - collapses whitespace
     */
    public static function text(?string $input): string
    {
        $value = trim((string) $input);

        if ($value === '') {
            return '';
        }

        // Normalize common "non-breaking" and thin spaces that often appear when users copy/paste.
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

        // Collapse all whitespace (tabs/newlines/multiple spaces) into a single space.
        return (string) preg_replace('/\s+/u', ' ', $value);
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
        if (stripos($providerSlug, '/providers/') !== false) {
            $path = parse_url($providerSlug, PHP_URL_PATH) ?: $providerSlug;

            $parts = preg_split('~/providers/~i', (string) $path, 2);
            $after = $parts[1] ?? '';

            $providerSlug = trim(explode('/', ltrim($after, '/'), 2)[0] ?? '');
        }

        // Allow pasted values like "demo-provider/" or "/providers/demo-provider/".
        $providerSlug = trim($providerSlug, '/');

        return mb_strtolower($providerSlug, 'UTF-8');
    }
}
