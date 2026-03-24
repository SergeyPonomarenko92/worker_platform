/**
 * Normalize currency code input (e.g. " uah " → "UAH").
 * Keeps only the first 3 characters after uppercasing and removing whitespace.
 */
export const normalizeCurrencyCode = (value) => {
    return (value ?? '')
        .toString()
        .toUpperCase()
        // remove all unicode whitespace
        .replace(/\s+/gu, '')
        .slice(0, 3)
}

/**
 * Normalize free-text inputs / query params.
 *
 * - Trims
 * - Collapses whitespace (incl. NBSP / thin spaces)
 * - Removes some invisible characters that can sneak in via copy/paste
 */
export const normalizeText = (value) => {
    return (value ?? '')
        .toString()
        // BOM, zero-width space, LTR/RTL marks, soft hyphen
        .replace(/[\uFEFF\u200B\u200E\u200F\u00AD]/gu, '')
        .replace(/\s+/gu, ' ')
        .trim()
}
