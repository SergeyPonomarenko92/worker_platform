<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Deal $deal): bool
    {
        // Only the client can leave a review.
        if ((int) $deal->client_user_id !== (int) $user->id) {
            return false;
        }

        // Only after completion.
        if ($deal->status !== 'completed') {
            return false;
        }

        // MVP anti-fraud: 1 review per deal.
        if ($deal->review()->exists()) {
            return false;
        }

        return true;
    }
}
