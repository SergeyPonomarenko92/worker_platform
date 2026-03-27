<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_list_when_query_is_too_short(): void
    {
        $this->getJson(route('api.providers', ['q' => 'д']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_suggests_only_active_business_profiles_that_are_visible_in_catalog(): void
    {
        $user = User::factory()->create();

        $visible = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'Demo Provider',
            'slug' => 'demo-provider',
            'is_active' => true,
        ]);

        Offer::factory()->for($visible)->create(['is_active' => true]);

        // Excluded: inactive business profile.
        $inactive = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'Inactive Provider',
            'slug' => 'inactive-provider',
            'is_active' => false,
        ]);

        Offer::factory()->for($inactive)->create(['is_active' => true]);

        // Excluded: active profile but no active offers.
        $noActiveOffers = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'name' => 'No Active Offers',
            'slug' => 'no-active-offers',
            'is_active' => true,
        ]);

        Offer::factory()->for($noActiveOffers)->create(['is_active' => false]);

        $this->getJson(route('api.providers', ['q' => 'demo']))
            ->assertOk()
            ->assertExactJson([
                ['name' => 'Demo Provider', 'slug' => 'demo-provider'],
            ]);
    }

    public function test_matches_by_slug_and_escapes_like_wildcards_in_query(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'name' => '100% Майстер',
            'slug' => '100%-master',
            'is_active' => true,
        ]);

        Offer::factory()->for($profile)->create(['is_active' => true]);

        $this->getJson(route('api.providers', ['q' => '100%']))
            ->assertOk()
            ->assertExactJson([
                ['name' => '100% Майстер', 'slug' => '100%-master'],
            ]);
    }
}
