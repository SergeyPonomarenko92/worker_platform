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

    public function test_returns_empty_array_when_query_is_too_short(): void
    {
        $this->getJson(route('api.cities', ['q' => 'к']))
            ->assertOk()
            ->assertExactJson([]);

        $this->getJson(route('api.cities', ['q' => ' ']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_suggests_only_cities_from_active_profiles_with_active_offers_and_matches_prefix_case_insensitive(): void
    {
        $provider = User::factory()->create();

        $activeVisible = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'is_active' => true,
            'city' => 'Київ',
        ]);
        Offer::factory()->create([
            'business_profile_id' => $activeVisible->id,
            'is_active' => true,
        ]);

        $activeButNoActiveOffers = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'is_active' => true,
            'city' => 'Кирилівка',
        ]);
        Offer::factory()->create([
            'business_profile_id' => $activeButNoActiveOffers->id,
            'is_active' => false,
        ]);

        $inactiveProfile = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'is_active' => false,
            'city' => 'Київ',
        ]);
        Offer::factory()->create([
            'business_profile_id' => $inactiveProfile->id,
            'is_active' => true,
        ]);

        // Another visible city with different case.
        $anotherVisible = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'is_active' => true,
            'city' => 'киборгоград',
        ]);
        Offer::factory()->create([
            'business_profile_id' => $anotherVisible->id,
            'is_active' => true,
        ]);

        $resp = $this->getJson(route('api.cities', ['q' => "  КИ  "]))
            ->assertOk()
            ->assertJsonIsArray()
            ->assertJsonCount(2);

        $cities = $resp->json();
        sort($cities);

        $this->assertSame([
            'Київ',
            'киборгоград',
        ], $cities);
    }
}
