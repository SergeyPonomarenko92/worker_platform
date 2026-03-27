<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogCategorySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_suggestions_returns_sorted_visible_categories_by_name_match_with_path(): void
    {
        $bp = BusinessProfile::factory()->create(['is_active' => true]);

        $parent = Category::factory()->create(['name' => 'Будівництво']);
        $child = Category::factory()->create(['name' => 'Електрика', 'parent_id' => $parent->id]);
        $electronics = Category::factory()->create(['name' => 'Електроніка']);

        // Only categories with active offers under active providers should be suggested.
        Offer::factory()->create(['business_profile_id' => $bp->id, 'category_id' => $child->id, 'is_active' => true]);
        Offer::factory()->create(['business_profile_id' => $bp->id, 'category_id' => $electronics->id, 'is_active' => true]);
        Offer::factory()->inactive()->create(['business_profile_id' => $bp->id, 'category_id' => $parent->id]);

        // Case-insensitive contains match.
        $this->getJson(route('catalog.categories', ['q' => 'елект']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Електрика')
            ->assertJsonPath('data.0.path', 'Будівництво → Електрика')
            ->assertJsonPath('data.1.name', 'Електроніка')
            ->assertJsonPath('data.1.path', 'Електроніка');

        // Too short query => empty.
        $this->getJson(route('catalog.categories', ['q' => 'е']))
            ->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_category_suggestions_escapes_like_wildcards(): void
    {
        $bp = BusinessProfile::factory()->create(['is_active' => true]);

        $pct = Category::factory()->create(['name' => '100% гарантія']);
        $plain = Category::factory()->create(['name' => '1000 гарантія']);

        Offer::factory()->create(['business_profile_id' => $bp->id, 'category_id' => $pct->id, 'is_active' => true]);
        Offer::factory()->create(['business_profile_id' => $bp->id, 'category_id' => $plain->id, 'is_active' => true]);

        // If wildcards were not escaped, "100%" would match both rows.
        $this->getJson(route('catalog.categories', ['q' => '100%']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '100% гарантія');
    }

    public function test_category_suggestions_eager_loads_ancestors_to_avoid_n_plus_one_when_building_paths(): void
    {
        $bp = BusinessProfile::factory()->create(['is_active' => true]);

        // Build a deep chain: root -> l1 -> ... -> l7, then multiple leaves under l7.
        $root = Category::factory()->create(['name' => 'Рівень 0']);
        $current = $root;

        for ($i = 1; $i <= 7; $i++) {
            $current = Category::factory()->create([
                'name' => "Рівень {$i}",
                'parent_id' => $current->id,
            ]);
        }

        $leafCategories = collect();
        for ($i = 1; $i <= 5; $i++) {
            $leaf = Category::factory()->create([
                'name' => "Електрика {$i}",
                'parent_id' => $current->id,
            ]);
            $leafCategories->push($leaf);

            Offer::factory()->create([
                'business_profile_id' => $bp->id,
                'category_id' => $leaf->id,
                'is_active' => true,
            ]);
        }

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $this->getJson(route('catalog.categories', ['q' => 'елект']))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.path', 'Рівень 0 → Рівень 1 → Рівень 2 → Рівень 3 → Рівень 4 → Рівень 5 → Рівень 6 → Рівень 7 → Електрика 1');

        // With eager-loading of ancestors, this endpoint should not issue one query per leaf per ancestor.
        // We allow some overhead for eager-load queries, but it should stay well below the naive N+1 case.
        $this->assertLessThanOrEqual(20, $queryCount);
    }
}
