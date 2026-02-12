<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_delete_story(): void
    {
        $user = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $user->id]);

        // Create
        $this->actingAs($user)
            ->post(route('dashboard.stories.store', $profile), [
                'media_path' => 'demo/story.jpg',
                'caption' => 'Hello',
                'expires_at' => now()->addDay()->toDateTimeString(),
            ])
            ->assertRedirect(route('dashboard.stories.index', $profile));

        $story = Story::query()->where('business_profile_id', $profile->id)->firstOrFail();
        $this->assertSame('demo/story.jpg', $story->media_path);

        // Update
        $this->actingAs($user)
            ->patch(route('dashboard.stories.update', [$profile, $story]), [
                'media_path' => 'demo/story2.jpg',
                'caption' => 'Updated',
                'expires_at' => now()->addDays(2)->toDateTimeString(),
            ])
            ->assertRedirect();

        $story->refresh();
        $this->assertSame('demo/story2.jpg', $story->media_path);

        // Delete
        $this->actingAs($user)
            ->delete(route('dashboard.stories.destroy', [$profile, $story]))
            ->assertRedirect(route('dashboard.stories.index', $profile));

        $this->assertDatabaseMissing('stories', [
            'id' => $story->id,
        ]);
    }
}
