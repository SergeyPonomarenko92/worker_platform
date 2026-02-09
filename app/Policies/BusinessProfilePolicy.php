<?php

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\User;

class BusinessProfilePolicy
{
    public function update(User $user, BusinessProfile $businessProfile): bool
    {
        return $businessProfile->user_id === $user->id;
    }
}
