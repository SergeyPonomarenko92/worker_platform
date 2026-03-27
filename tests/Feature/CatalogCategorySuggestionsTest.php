<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCategorySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_suggestions_returns_sorted_categories_by_name_match(): void
    {
        Category::factory()->create(['name' => 'Електрика']);
        Category::factory()->create(['name' => 'Електроніка']);
        Category::factory()->create(['name' => 'Будівництво']);

        // Case-insensitive contains match.
        $this->getJson(route('catalog.categories', ['q' => 'елект']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Електрика')
            ->assertJsonPath('data.1.name', 'Електроніка');

        // Too short query => empty.
        $this->getJson(route('catalog.categories', ['q' => 'е']))
            ->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_category_suggestions_escapes_like_wildcards(): void
    {
        Category::factory()->create(['name' => '100% гарантія']);
        Category::factory()->create(['name' => '1000 гарантія']);

        // If wildcards were not escaped, "100%" would match both rows.
        $this->getJson(route('catalog.categories', ['q' => '100%']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '100% гарантія');
    }
}
