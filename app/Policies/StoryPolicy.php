<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function update(User $user, Story $story): bool
    {
        // Don't rely on possibly-unloaded relationships.
        return $story->businessProfile()->where('user_id', $user->id)->exists();
    }

    public function delete(User $user, Story $story): bool
    {
        return $this->update($user, $story);
    }
}
