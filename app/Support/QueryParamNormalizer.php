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
        // - Mongolian vowel separator (U+180E)
        // - EN quad (U+2000)
        // - EM quad (U+2001)
        // - EN space (U+2002)
        // - EM space (U+2003)
        // - Three-per-em space (U+2004)
        // - Four-per-em space (U+2005)
        // - Six-per-em space (U+2006)
        // - Figure space (U+2007)
        // - Punctuation space (U+2008)
        // - Thin space (U+2009)
        // - Hair space (U+200A)
        // - Narrow no-break space (U+202F)
        // - Medium mathematical space (U+205F)
        // - Ideographic space (U+3000)
        $value = str_replace([
            "\u{00A0}",
            // Mongolian vowel separator (deprecated but still appears in some copy/paste flows).
            "\u{180E}",
            "\u{2000}",
            "\u{2001}",
            "\u{2002}",
            "\u{2003}",
            "\u{2004}",
            "\u{2005}",
            "\u{2006}",
            "\u{2007}",
            "\u{2008}",
            "\u{2009}",
            "\u{200A}",
            "\u{202F}",
            "\u{205F}",
            "\u{3000}",
        ], ' ', $value);

        // Normalize invisible separators that are not matched by trim() and can break search.
        // Treat them as spaces to avoid accidentally concatenating words.
        // - Zero width space (U+200B)
        // - Zero width non-joiner (U+200C)
        // - Zero width joiner (U+200D)
        // - Left-to-right mark (U+200E)
        // - Right-to-left mark (U+200F)
        // - Bidi embedding/override controls (U+202A..U+202E)
        // - Word joiner (U+2060)
        // - Bidi isolate controls (U+2066..U+2069)
        // - Zero width no-break space / BOM (U+FEFF)
        $value = str_replace([
            "\u{200B}",
            "\u{200C}",
            "\u{200D}",
            "\u{200E}",
            "\u{200F}",
            "\u{202A}",
            "\u{202B}",
            "\u{202C}",
            "\u{202D}",
            "\u{202E}",
            "\u{2060}",
            "\u{2066}",
            "\u{2067}",
            "\u{2068}",
            "\u{2069}",
            "\u{FEFF}",
        ], ' ', $value);

        // Soft hyphen (U+00AD) sometimes appears inside words when users copy text from PDFs/websites.
        // It should not affect search queries, so strip it entirely.
        $value = str_replace("\u{00AD}", '', $value);

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

        // If a user pastes a catalog URL like:
        // - "/catalog?provider=demo-provider"
        // - "https://example.test/catalog?provider=demo-provider"
        // prefer extracting the actual provider value from the query string.
        // Without this, parse_url(..., PHP_URL_PATH) would turn it into "/catalog".
        $parsedQuery = parse_url($providerInput, PHP_URL_QUERY);
        if (is_string($parsedQuery) && $parsedQuery !== '') {
            parse_str($parsedQuery, $query);

            if (is_array($query) && isset($query['provider']) && is_string($query['provider'])) {
                $fromQuery = self::text($query['provider']);
                if ($fromQuery !== '') {
                    $providerInput = $fromQuery;
                }
            }
        }

        $providerSlug = $providerInput;

        // Strip query/hash fragments even when users paste a plain slug like
        // "demo-provider?ref=catalog".
        $providerSlug = parse_url($providerSlug, PHP_URL_PATH) ?: $providerSlug;

        // Some platforms copy links with percent-encoded characters in the path.
        // For example: "/providers/demo%2Dprovider".
        // Decode them to match stored slugs.
        $providerSlug = rawurldecode($providerSlug);

        // Provider filter can be either a slug ("demo-provider") or a pasted provider URL.
        // Be robust to different casing in the path ("/Providers/...") when users copy/paste.
        // Also handle values without a leading slash ("providers/demo-provider") which can appear
        // when users copy relative URLs from some apps.
        if (preg_match('~(^|/)providers/~i', $providerSlug) === 1) {
            $parts = preg_split('~providers/~i', (string) $providerSlug, 2);
            $after = $parts[1] ?? '';

            $providerSlug = trim(explode('/', ltrim($after, '/'), 2)[0] ?? '');
        }

        $providerSlug = rawurldecode($providerSlug);

        // Allow pasted values like "demo-provider/" or "/providers/demo-provider/".

        // Users may paste the slug wrapped in punctuation or quotes,
        // e.g. "(demo-provider)", "\"demo-provider\"", "demo-provider,".
        // Keep this trimming conservative: strip only common outer punctuation.
        $providerSlug = trim($providerSlug, "/ \t\n\r\0\x0B\"'.,;:()[]{}<>@");

        $providerSlug = trim($providerSlug, '/');

        return mb_strtolower($providerSlug, 'UTF-8');
    }

    /**
     * Normalize an integer-like query param that may contain formatting separators.
     *
     * Examples (should become 1000):
     * - "1 000"
     * - "1\u00A0000" (NBSP)
     * - "1\u202F000" (narrow no-break space)
     * - "1'000" (apostrophe)
     *
     * Returns null when the value is empty or not an unsigned integer.
     */
    public static function unsignedInt(?string $input): ?int
    {
        $value = self::text($input);

        if ($value === '') {
            return null;
        }

        // Allow common thousands separators.
        // We normalize unicode whitespace in text() already, so only plain spaces remain.
        $value = str_replace([' ', "'", '’', 'ʼ'], '', $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $value) !== 1) {
            return null;
        }

        return (int) $value;
    }
}
