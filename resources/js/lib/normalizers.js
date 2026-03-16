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
