<?php

namespace App\Http\Requests\Dashboard;

class StoreBusinessProfileRequest extends BusinessProfileFormRequest
{
    public function authorize(): bool
    {
        // Controller handles auth for store (any logged-in user).
        return $this->user() !== null;
    }

    public function normalized(): array
    {
        $data = $this->normalizedValidatedData();

        // Store default behavior: active by default.
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        return $data;
    }

}

