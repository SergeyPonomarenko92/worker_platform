<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_list_when_query_is_too_short(): void
    {
        $this->getJson(route('api.cities', ['q' => 'к']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_returns_distinct_city_suggestions_for_visible_catalog_providers_only(): void
    {
        $provider = User::factory()->create();

        $kyiv = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'city' => 'Київ',
            'is_active' => true,
        ]);

        Offer::factory()->for($kyiv)->create([
            'is_active' => true,
        ]);

        // Should be excluded: no active offers (not visible in catalog).
        $noOffers = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'city' => 'Київ',
            'is_active' => true,
        ]);

        Offer::factory()->for($noOffers)->create([
            'is_active' => false,
        ]);

        // Should be excluded: inactive business profile.
        $inactive = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'city' => 'Київ',
            'is_active' => false,
        ]);

        Offer::factory()->for($inactive)->create([
            'is_active' => true,
        ]);

        // Should be excluded: empty/whitespace-only legacy data.
        $emptyCity = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'city' => '   ',
            'is_active' => true,
        ]);

        Offer::factory()->for($emptyCity)->create([
            'is_active' => true,
        ]);

        $this->getJson(route('api.cities', ['q' => 'ки']))
            ->assertOk()
            ->assertJson(['Київ'])
            ->assertJsonCount(1);
    }

    public function test_escapes_like_wildcards_in_query(): void
    {
        $provider = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'city' => '100% Місто',
            'is_active' => true,
        ]);

        Offer::factory()->for($profile)->create(['is_active' => true]);

        // If '%' isn't escaped, this would match a lot more than the intended prefix.
        $this->getJson(route('api.cities', ['q' => '100%']))
            ->assertOk()
            ->assertExactJson(['100% Місто']);
    }
}
