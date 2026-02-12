<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\PortfolioPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_delete_portfolio_post(): void
    {
        $user = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $user->id]);

        // Create
        $this->actingAs($user)
            ->post(route('dashboard.portfolio-posts.store', $profile), [
                'title' => 'My work',
                'body' => 'Details',
                'published_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect(route('dashboard.portfolio-posts.index', $profile));

        $post = PortfolioPost::query()->where('business_profile_id', $profile->id)->firstOrFail();
        $this->assertSame('My work', $post->title);

        // Update
        $this->actingAs($user)
            ->patch(route('dashboard.portfolio-posts.update', [$profile, $post]), [
                'title' => 'My work updated',
                'body' => 'New details',
                'published_at' => now()->addHour()->toDateTimeString(),
            ])
            ->assertRedirect();

        $post->refresh();
        $this->assertSame('My work updated', $post->title);

        // Delete
        $this->actingAs($user)
            ->delete(route('dashboard.portfolio-posts.destroy', [$profile, $post]))
            ->assertRedirect(route('dashboard.portfolio-posts.index', $profile));

        $this->assertDatabaseMissing('portfolio_posts', [
            'id' => $post->id,
        ]);
    }
}
