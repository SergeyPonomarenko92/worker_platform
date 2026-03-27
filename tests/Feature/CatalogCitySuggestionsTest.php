<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCitySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_suggestions_returns_distinct_sorted_cities_for_active_profiles(): void
    {
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Київ']);
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Київ']); // duplicate
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Кременчук']);
        BusinessProfile::factory()->create(['is_active' => false, 'city' => 'Київ']); // inactive
        BusinessProfile::factory()->create(['is_active' => true, 'city' => null]); // empty

        $this->getJson(route('catalog.cities', ['q' => 'Ки']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertJson([
                'data' => ['Київ'],
            ]);

        $this->getJson(route('catalog.cities', ['q' => 'К']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertJson([
                'data' => [],
            ]);

        $this->getJson(route('catalog.cities', ['q' => 'Кр']))
            ->assertOk()
            ->assertJson([
                'data' => ['Кременчук'],
            ]);
    }

    public function test_city_suggestions_escapes_like_wildcards(): void
    {
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Ки%їв']);
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Київ']);

        // If wildcards were not escaped, "Ки%" would match both rows.
        $this->getJson(route('catalog.cities', ['q' => 'Ки%']))
            ->assertOk()
            ->assertJson([
                'data' => ['Ки%їв'],
            ]);
    }

    public function test_city_suggestions_normalizes_unicode_whitespace_in_query(): void
    {
        BusinessProfile::factory()->create(['is_active' => true, 'city' => 'Київ']);

        // QueryParamNormalizer::text() should trim/collapse unicode whitespace.
        // Ensure suggestions still work for copy/paste inputs.
        $q = "\u{00A0}  Ки\u{00A0}"; // NBSP + spaces + trailing NBSP

        $this->getJson(route('catalog.cities', ['q' => $q]))
            ->assertOk()
            ->assertJson([
                'data' => ['Київ'],
            ]);
    }
}
