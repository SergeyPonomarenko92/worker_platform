<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
