<?php

namespace App\Policies;

use App\Models\PortfolioPost;
use App\Models\User;

class PortfolioPostPolicy
{
    public function update(User $user, PortfolioPost $portfolioPost): bool
    {
        // Don't rely on possibly-unloaded relationships.
        return $portfolioPost->businessProfile()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, PortfolioPost $portfolioPost): bool
    {
        return $this->update($user, $portfolioPost);
    }
}
