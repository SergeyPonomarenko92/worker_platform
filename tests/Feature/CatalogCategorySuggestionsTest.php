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
            ->assertHeader('Cache-Control', 'max-age=300, public')
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

        // Create 10 distinct deep category chains that all match the same query.
        // This test is meant to catch accidental N+1 when building category paths.
        for ($i = 1; $i <= 10; $i++) {
            $leaf = $this->createCategoryChain([
                'root' => "Послуги {$i}",
                'level1' => "Ремонт {$i}",
                'level2' => "Побут {$i}",
                'level3' => "Майстри {$i}",
                'leaf' => "Категорія {$i}",
            ]);

            Offer::factory()->create([
                'business_profile_id' => $bp->id,
                'category_id' => $leaf->id,
                'is_active' => true,
            ]);
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $response = $this->getJson(route('catalog.categories', ['q' => 'катег']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public');

        $data = $response->json('data');

        $this->assertCount(10, $data);
        $this->assertSame('Послуги 1 → Ремонт 1 → Побут 1 → Майстри 1 → Категорія 1', $data[0]['path']);

        $categoryQueries = array_values(array_filter($queries, function (string $sql) {
            return str_contains($sql, 'from "categories"');
        }));

        // With nested eager-loading we expect a small, bounded number of category queries.
        // Without eager-loading, this would explode (parents would be lazy-loaded per item).
        $this->assertLessThanOrEqual(
            15,
            count($categoryQueries),
            'Category suggestions should not trigger N+1 queries for parent categories.'
        );
    }

    private function createCategoryChain(array $names): Category
    {
        $root = Category::factory()->create([
            'parent_id' => null,
            'name' => $names['root'],
            'slug' => 'root-'.md5($names['root']),
        ]);

        $level1 = Category::factory()->create([
            'parent_id' => $root->id,
            'name' => $names['level1'],
            'slug' => 'l1-'.md5($names['level1']),
        ]);

        $level2 = Category::factory()->create([
            'parent_id' => $level1->id,
            'name' => $names['level2'],
            'slug' => 'l2-'.md5($names['level2']),
        ]);

        $level3 = Category::factory()->create([
            'parent_id' => $level2->id,
            'name' => $names['level3'],
            'slug' => 'l3-'.md5($names['level3']),
        ]);

        return Category::factory()->create([
            'parent_id' => $level3->id,
            'name' => $names['leaf'],
            'slug' => 'leaf-'.md5($names['leaf']),
        ]);
    }
}
