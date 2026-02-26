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

        // Normalize common "non-breaking" spaces that often appear when users copy/paste.
        // - NBSP (U+00A0)
        // - Narrow no-break space (U+202F)
        $value = str_replace([
            "\u{00A0}",
            "\u{202F}",
        ], ' ', $value);

        // Collapse all whitespace (tabs/newlines/multiple spaces) into a single space.
        return preg_replace('/\s+/u', ' ', $value);
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
        if (str_contains($providerSlug, '/providers/')) {
            $path = parse_url($providerSlug, PHP_URL_PATH) ?: $providerSlug;
            $after = explode('/providers/', $path, 2)[1] ?? '';
            $providerSlug = trim(explode('/', ltrim($after, '/'), 2)[0] ?? '');
        }

        // Allow pasted values like "demo-provider/" or "/providers/demo-provider/".
        $providerSlug = trim($providerSlug, '/');

        return mb_strtolower($providerSlug, 'UTF-8');
    }
}
