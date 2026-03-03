<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryScopeBindingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_routes_return_404_when_story_does_not_belong_to_business_profile(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProfile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $storyFromOtherProfile = Story::factory()->create([
            'business_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.stories.edit', [$profile, $storyFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.stories.update', [$profile, $storyFromOtherProfile]), [
                'media_path' => $storyFromOtherProfile->media_path,
                'caption' => $storyFromOtherProfile->caption,
                'expires_at' => optional($storyFromOtherProfile->expires_at)->toDateTimeString(),
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('dashboard.stories.destroy', [$profile, $storyFromOtherProfile]))
            ->assertNotFound();
    }
}
