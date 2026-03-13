<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_empty_array_for_short_query(): void
    {
        $this->getJson(route('api.categories', ['q' => 'a']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_it_suggests_categories_by_fuzzy_name_match_and_includes_path(): void
    {
        $parent = Category::factory()->create(['name' => 'Ремонт', 'slug' => 'remont']);
        $child = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Електрика', 'slug' => 'elektryka']);
        $other = Category::factory()->create(['name' => 'Прибирання', 'slug' => 'prybyrannia']);

        $profile = BusinessProfile::factory()->create(['is_active' => true]);

        // Active offer in the child category makes it eligible.
        Offer::factory()->create([
            'business_profile_id' => $profile->id,
            'category_id' => $child->id,
            'is_active' => true,
        ]);

        // Inactive offer in the other category must not make it eligible.
        Offer::factory()->create([
            'business_profile_id' => $profile->id,
            'category_id' => $other->id,
            'is_active' => false,
        ]);

        $this->getJson(route('api.categories', ['q' => 'лек']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $child->id,
                'name' => 'Електрика',
                'slug' => 'elektryka',
                'path' => 'Ремонт → Електрика',
            ]);
    }
}
