<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\PortfolioPost;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderCabinetAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_edit_someone_elses_business_profile(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.business-profiles.edit', $profile))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => 'Hacked',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_edit_or_delete_someone_elses_offer(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $offer = Offer::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.offers.edit', [$profile, $offer]))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('dashboard.offers.update', [$profile, $offer]), [
                'type' => 'service',
                'title' => 'Nope',
                'currency' => 'UAH',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('dashboard.offers.destroy', [$profile, $offer]))
            ->assertForbidden();
    }

    public function test_user_cannot_edit_or_delete_someone_elses_portfolio_post(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $post = PortfolioPost::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.portfolio-posts.edit', [$profile, $post]))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('dashboard.portfolio-posts.update', [$profile, $post]), [
                'title' => 'Nope',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('dashboard.portfolio-posts.destroy', [$profile, $post]))
            ->assertForbidden();
    }

    public function test_user_cannot_delete_someone_elses_story(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $story = Story::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($intruder)
            ->delete(route('dashboard.stories.destroy', [$profile, $story]))
            ->assertForbidden();
    }
}
