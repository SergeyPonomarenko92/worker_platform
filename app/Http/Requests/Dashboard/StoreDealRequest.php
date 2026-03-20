<?php

namespace App\Http\Requests\Dashboard;

use App\Support\QueryParamNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled in controller via policies.
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalize optional numeric/select fields from HTML forms ("" -> null)
        $this->merge([
            // Be robust to copy/paste and different casing.
            // (exists:users,email) is usually case-sensitive depending on collation.
            'client_email' => QueryParamNormalizer::email((string) $this->input('client_email')),
            'offer_id' => $this->input('offer_id') ?: null,
            'agreed_price' => $this->input('agreed_price') === '' ? null : $this->input('agreed_price'),
            'currency' => strtoupper(QueryParamNormalizer::text((string) $this->input('currency'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'client_email' => ['required', 'email', 'exists:users,email'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            // Stored as unsignedInteger in DB, so decimals are not allowed.
            'agreed_price' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['UAH', 'USD', 'EUR'])],
            'status' => ['required', 'in:draft,in_progress,completed,cancelled'],
        ];
    }
}
