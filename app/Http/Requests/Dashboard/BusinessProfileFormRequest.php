<?php

namespace App\Http\Requests\Dashboard;

use App\Support\BusinessProfileRequestNormalizer;
use App\Support\ContactFieldNormalizer;
use App\Support\HttpUrlValidator;
use Illuminate\Foundation\Http\FormRequest;

abstract class BusinessProfileFormRequest extends FormRequest
{
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

    protected function normalizedValidatedData(): array
    {
        $data = $this->validated();

        $data['about'] = BusinessProfileRequestNormalizer::nullableText($data['about'] ?? null);
        $data['country_code'] = BusinessProfileRequestNormalizer::countryCode($data['country_code'] ?? null);
        $data['city'] = BusinessProfileRequestNormalizer::nullableText($data['city'] ?? null);
        $data['address'] = BusinessProfileRequestNormalizer::nullableText($data['address'] ?? null);

        $data['website'] = ContactFieldNormalizer::website($data['website'] ?? null);
        HttpUrlValidator::validateOrFail($data['website'], 'website');

        $data['phone'] = ContactFieldNormalizer::phone($data['phone'] ?? null);

        return $data;
    }
}
