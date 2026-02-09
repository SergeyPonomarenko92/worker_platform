<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function update(User $user, Offer $offer): bool
    {
        // Don't rely on possibly-unloaded relationships.
        return $offer->businessProfile()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $this->update($user, $offer);
    }
}
