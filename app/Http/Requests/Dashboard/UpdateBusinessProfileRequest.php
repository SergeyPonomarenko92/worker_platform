<?php

namespace App\Http\Requests\Dashboard;

use App\Models\BusinessProfile;

class UpdateBusinessProfileRequest extends BusinessProfileFormRequest
{
    public function authorize(): bool
    {
        /** @var BusinessProfile|null $profile */
        $profile = $this->route('businessProfile');

        return $profile !== null && $this->user()?->can('update', $profile) === true;
    }

    public function normalized(): array
    {
        $data = $this->normalizedValidatedData();

        // Update behavior: only cast when present (so missing checkbox does not overwrite).
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }

}

