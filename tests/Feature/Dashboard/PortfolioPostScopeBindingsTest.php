<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\PortfolioPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPostScopeBindingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_post_routes_return_404_when_portfolio_post_does_not_belong_to_business_profile(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProfile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $postFromOtherProfile = PortfolioPost::factory()->create([
            'business_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.portfolio-posts.edit', [$profile, $postFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.portfolio-posts.update', [$profile, $postFromOtherProfile]), [
                'title' => $postFromOtherProfile->title,
                'body' => $postFromOtherProfile->body,
                'published_at' => optional($postFromOtherProfile->published_at)->toDateTimeString(),
            ])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('dashboard.portfolio-posts.destroy', [$profile, $postFromOtherProfile]))
            ->assertNotFound();
    }
}
