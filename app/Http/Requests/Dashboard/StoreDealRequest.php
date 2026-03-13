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
            'client_email' => mb_strtolower(QueryParamNormalizer::text((string) $this->input('client_email')), 'UTF-8'),
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
            'agreed_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['UAH', 'USD', 'EUR'])],
            'status' => ['required', 'in:draft,in_progress,completed,cancelled'],
        ];
    }
}
