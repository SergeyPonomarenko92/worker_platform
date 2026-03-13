<?php

namespace App\Http\Requests\Dashboard;

use App\Support\QueryParamNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class OfferFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:service,product'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'price_to' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when($this->filled('price_from'), ['gte:price_from']),
            ],
            'currency' => ['required', 'string', 'size:3', Rule::in(['UAH', 'USD', 'EUR'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize text/numeric/select fields from HTML forms.
        // - text: trim/collapse whitespace + NBSP
        // - optional numeric/select: "" -> null
        $this->merge([
            'type' => is_string($this->input('type'))
                ? strtolower(trim(QueryParamNormalizer::text($this->input('type'))))
                : $this->input('type'),

            'title' => QueryParamNormalizer::text($this->input('title')),
            'description' => QueryParamNormalizer::text($this->input('description')),

            'category_id' => $this->input('category_id') ?: null,
            'price_from' => $this->input('price_from') === '' ? null : $this->input('price_from'),
            'price_to' => $this->input('price_to') === '' ? null : $this->input('price_to'),

            // Allow users to paste currency with extra whitespace (e.g. " uah ", "u a h", NBSPs)
            // and accept case-insensitive input.
            'currency' => is_string($this->input('currency'))
                ? strtoupper(preg_replace('/\s+/u', '', QueryParamNormalizer::text($this->input('currency'))))
                : $this->input('currency'),
        ]);
    }

    public function normalized(): array
    {
        $data = $this->validated();

        if (($data['description'] ?? null) === '') {
            $data['description'] = null;
        }

        $data['currency'] = strtoupper(trim($data['currency']));

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
