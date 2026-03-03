<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class HttpUrlValidator
{
    /**
     * @throws ValidationException
     */
    public static function validateOrFail(?string $url, string $field = 'website'): void
    {
        if ($url === null) {
            return;
        }

        // Extra safety: do not persist non-URL / non-http(s) values (e.g. "javascript:...").
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages([
                $field => 'Некоректний URL вебсайту.',
            ]);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                $field => 'URL вебсайту має починатися з http:// або https://',
            ]);
        }
    }
}
