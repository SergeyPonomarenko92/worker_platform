<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProfile;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_empty_array_for_short_query(): void
    {
        $this->getJson('/api/cities?q=К')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_it_suggests_distinct_cities_for_active_profiles_with_active_offers(): void
    {
        $bpKyiv = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        Offer::factory()->for($bpKyiv)->create(['is_active' => true]);

        $bpKyiv2 = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        Offer::factory()->for($bpKyiv2)->create(['is_active' => true]);

        $bpLviv = BusinessProfile::factory()->create(['city' => 'Львів', 'is_active' => true]);
        Offer::factory()->for($bpLviv)->create(['is_active' => true]);

        // Should be ignored: inactive offer.
        $bpKyivInactiveOffer = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        Offer::factory()->for($bpKyivInactiveOffer)->create(['is_active' => false]);

        // Should be ignored: inactive profile.
        $bpKyivInactiveProfile = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => false]);
        Offer::factory()->for($bpKyivInactiveProfile)->create(['is_active' => true]);

        $this->getJson('/api/cities?q=Ки')
            ->assertOk()
            ->assertJson(['Київ']);

        $this->getJson('/api/cities?q=Л')
            ->assertOk()
            ->assertExactJson([]);

        $this->getJson('/api/cities?q=Ль')
            ->assertOk()
            ->assertJson(['Львів']);
    }

    public function test_it_is_case_insensitive_and_prefix_based(): void
    {
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        Offer::factory()->for($bp)->create(['is_active' => true]);

        $this->getJson('/api/cities?q=ки')
            ->assertOk()
            ->assertExactJson(['Київ']);

        $this->getJson('/api/cities?q=їв')
            ->assertOk()
            ->assertExactJson([]);
    }
}
