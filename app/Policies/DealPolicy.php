<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function update(User $user, Deal $deal): bool
    {
        // Provider can manage deals only within their business profile.
        return $deal->businessProfile()->where('user_id', $user->id)->exists();
    }

    public function view(User $user, Deal $deal): bool
    {
        return $this->update($user, $deal);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $this->update($user, $deal);
    }
}
