<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_swaps_price_bounds_when_price_to_is_less_than_price_from(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Cheap offer',
            'is_active' => true,
            'price_from' => 200,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Expensive offer',
            'is_active' => true,
            'price_from' => 2000,
        ]);

        $this
            ->get('/catalog?price_from=500&price_to=100')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Cheap offer')
            );
    }

    public function test_catalog_filters_by_city_prefix_case_insensitive(): void
    {
        $cat = Category::factory()->create(['name' => 'Електрик']);

        $kyiv = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        $lviv = BusinessProfile::factory()->create(['city' => 'Львів', 'is_active' => true]);

        Offer::factory()->for($kyiv)->create([
            'category_id' => $cat->id,
            'title' => 'Київ оффер',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($lviv)->create([
            'category_id' => $cat->id,
            'title' => 'Львів оффер',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?city=%D0%BA%D0%B8') // "ки"
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Київ оффер')
            );
    }

    public function test_catalog_filters_by_price_range_and_excludes_null_price_when_filtering(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'No price offer',
            'is_active' => true,
            'price_from' => null,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Cheap offer',
            'is_active' => true,
            'price_from' => 200,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Expensive offer',
            'is_active' => true,
            'price_from' => 2000,
        ]);

        $this
            ->get('/catalog?price_from=100&price_to=500')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Cheap offer')
            );
    }

    public function test_catalog_can_include_offers_without_price_when_filtering_by_price(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'No price offer',
            'is_active' => true,
            'price_from' => null,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Cheap offer',
            'is_active' => true,
            'price_from' => 200,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Expensive offer',
            'is_active' => true,
            'price_from' => 2000,
        ]);

        $this
            ->get('/catalog?price_from=100&price_to=500&include_no_price=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 2)
            );
    }

    public function test_catalog_category_filter_includes_child_categories(): void
    {
        $parent = Category::factory()->create(['name' => 'Ремонт', 'parent_id' => null]);
        $child = Category::factory()->create(['name' => 'Електрика', 'parent_id' => $parent->id]);

        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $child->id,
            'title' => 'Child offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $parent->id,
            'title' => 'Parent offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?category_id='.$parent->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 2)
            );
    }
}
