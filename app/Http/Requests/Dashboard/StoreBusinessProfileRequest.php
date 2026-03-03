<?php

namespace App\Http\Requests\Dashboard;

use App\Support\ContactFieldNormalizer;
use App\Support\HttpUrlValidator;
use App\Support\QueryParamNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Controller handles auth for store (any logged-in user).
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            // Allow a bit more input length, because we normalize to ISO-3166 alpha-2 anyway (e.g. "u a", "USA").
            'country_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function normalized(): array
    {
        $data = $this->validated();

        $data['about'] = self::nullableText($data['about'] ?? null);
        $data['country_code'] = self::countryCode($data['country_code'] ?? null);
        $data['city'] = self::nullableText($data['city'] ?? null);
        $data['address'] = self::nullableText($data['address'] ?? null);

        $data['website'] = ContactFieldNormalizer::website($data['website'] ?? null);
        HttpUrlValidator::validateOrFail($data['website'], 'website');

        $data['phone'] = ContactFieldNormalizer::phone($data['phone'] ?? null);

        // Store default behavior: active by default.
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        return $data;
    }

    private static function nullableText(?string $raw): ?string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return null;
        }

        return $v;
    }

    private static function countryCode(?string $raw): string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return 'UA';
        }

        // Robustness: accept inputs like "u a", "UA!", "USA" etc.
        // Keep only ASCII letters and take the first two characters.
        $v = preg_replace('/[^A-Za-z]/', '', $v) ?? '';
        $v = strtoupper($v);

        if (strlen($v) < 2) {
            return 'UA';
        }

        return substr($v, 0, 2);
    }
}
