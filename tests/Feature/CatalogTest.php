<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_shows_only_active_offers_from_active_business_profiles(): void
    {
        $cat = Category::factory()->create();

        $activeBp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);
        $inactiveBp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => false]);

        Offer::factory()->for($activeBp)->create([
            'category_id' => $cat->id,
            'title' => 'Active offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($activeBp)->create([
            'category_id' => $cat->id,
            'title' => 'Inactive offer',
            'is_active' => false,
            'price_from' => 100,
        ]);

        Offer::factory()->for($inactiveBp)->create([
            'category_id' => $cat->id,
            'title' => 'Inactive BP offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Active offer')
            );
    }

    public function test_catalog_sort_price_asc_orders_by_price_and_puts_null_prices_last(): void
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
            'title' => 'Mid offer',
            'is_active' => true,
            'price_from' => 200,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Cheap offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?sort=price_asc')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 3)
                ->where('offers.data.0.title', 'Cheap offer')
                ->where('offers.data.1.title', 'Mid offer')
                ->where('offers.data.2.title', 'No price offer')
            );
    }

    public function test_catalog_sort_price_desc_orders_by_price_and_puts_null_prices_last(): void
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
            'title' => 'Mid offer',
            'is_active' => true,
            'price_from' => 200,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Expensive offer',
            'is_active' => true,
            'price_from' => 1000,
        ]);

        $this
            ->get('/catalog?sort=price_desc')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 3)
                ->where('offers.data.0.title', 'Expensive offer')
                ->where('offers.data.1.title', 'Mid offer')
                ->where('offers.data.2.title', 'No price offer')
            );
    }

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
                ->where('filters.price_from', '100')
                ->where('filters.price_to', '500')
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

    public function test_catalog_normalizes_whitespace_in_city_filter(): void
    {
        $cat = Category::factory()->create(['name' => 'Електрик']);

        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Київ оффер',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?city=%20%20%D0%9A%D0%98%20%20') // "  КИ  "
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.city', 'КИ')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Київ оффер')
            );
    }

    public function test_catalog_normalizes_whitespace_in_q_filter(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Майстер руки',
            'description' => 'Швидко і якісно',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?q=%D0%BC%D0%B0%D0%B9%D1%81%D1%82%D0%B5%D1%80%20%20%20%D1%80%D1%83%D0%BA%D0%B8') // "майстер   руки"
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.q', 'майстер руки')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Майстер руки')
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
                ->where('offers.data', function ($offers) {
                    $offers = $offers instanceof \Illuminate\Support\Collection ? $offers->all() : (array) $offers;
                    $titles = array_map(fn ($o) => $o['title'] ?? null, $offers);
                    sort($titles);

                    return $titles === ['Cheap offer', 'No price offer'];
                })
            );
    }

    public function test_catalog_can_include_offers_without_price_when_filtering_by_only_price_from(): void
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
            ->get('/catalog?price_from=500&include_no_price=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 2)
                ->where('offers.data', function ($offers) {
                    $offers = $offers instanceof \Illuminate\Support\Collection ? $offers->all() : (array) $offers;
                    $titles = array_map(fn ($o) => $o['title'] ?? null, $offers);
                    sort($titles);

                    return $titles === ['Expensive offer', 'No price offer'];
                })
            );
    }

    public function test_catalog_can_include_offers_without_price_when_filtering_by_only_price_to(): void
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
            ->get('/catalog?price_to=500&include_no_price=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 2)
                ->where('offers.data', function ($offers) {
                    $offers = $offers instanceof \Illuminate\Support\Collection ? $offers->all() : (array) $offers;
                    $titles = array_map(fn ($o) => $o['title'] ?? null, $offers);
                    sort($titles);

                    return $titles === ['Cheap offer', 'No price offer'];
                })
            );
    }

    public function test_catalog_category_filter_includes_child_categories(): void
    {
        $parent = Category::factory()->create(['name' => 'Ремонт', 'parent_id' => null]);
        $child = Category::factory()->create(['name' => 'Електрика', 'parent_id' => $parent->id]);

        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        // Create parent first, then child -> newest ordering should put child first.
        Offer::factory()->for($bp)->create([
            'category_id' => $parent->id,
            'title' => 'Parent offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $child->id,
            'title' => 'Child offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?category_id='.$parent->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.category_id', (string) $parent->id)
                ->has('offers.data', 2)
                ->where('offers.data', function ($offers) {
                    $offers = $offers instanceof \Illuminate\Support\Collection ? $offers->all() : (array) $offers;
                    $titles = array_map(fn ($o) => $o['title'] ?? null, $offers);
                    sort($titles);

                    return $titles === ['Child offer', 'Parent offer'];
                })
            );
    }

    public function test_catalog_category_filter_includes_grandchild_categories(): void
    {
        $parent = Category::factory()->create(['name' => 'Ремонт', 'parent_id' => null]);
        $child = Category::factory()->create(['name' => 'Електрика', 'parent_id' => $parent->id]);
        $grandchild = Category::factory()->create(['name' => 'Проводка', 'parent_id' => $child->id]);

        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $parent->id,
            'title' => 'Parent offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $child->id,
            'title' => 'Child offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($bp)->create([
            'category_id' => $grandchild->id,
            'title' => 'Grandchild offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?category_id='.$parent->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.category_id', (string) $parent->id)
                ->has('offers.data', 3)
                ->where('offers.data', function ($offers) {
                    $offers = $offers instanceof \Illuminate\Support\Collection ? $offers->all() : (array) $offers;
                    $titles = array_map(fn ($o) => $o['title'] ?? null, $offers);
                    sort($titles);

                    return $titles === ['Child offer', 'Grandchild offer', 'Parent offer'];
                })
            );
    }

    public function test_catalog_include_no_price_is_ignored_without_price_bounds(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'No price offer',
            'is_active' => true,
            'price_from' => null,
        ]);

        $this
            ->get('/catalog?include_no_price=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.include_no_price', false)
            );
    }

    public function test_catalog_ignores_invalid_sort_and_falls_back_to_newest(): void
    {
        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp)->create([
            'category_id' => $cat->id,
            'title' => 'Some offer',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?sort=totally_invalid')
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('filters.sort', 'newest')
            );
    }

    public function test_catalog_q_filter_matches_business_profile_name(): void
    {
        $cat = Category::factory()->create();

        $bp1 = BusinessProfile::factory()->create(['name' => 'Супер Електрик', 'city' => 'Київ', 'is_active' => true]);
        $bp2 = BusinessProfile::factory()->create(['name' => 'Пекарня', 'city' => 'Київ', 'is_active' => true]);

        Offer::factory()->for($bp1)->create([
            'category_id' => $cat->id,
            'title' => 'Послуга 1',
            'description' => 'Опис',
            'is_active' => true,
            'price_from' => 100,
        ]);

        Offer::factory()->for($bp2)->create([
            'category_id' => $cat->id,
            'title' => 'Послуга 2',
            'description' => 'Опис',
            'is_active' => true,
            'price_from' => 100,
        ]);

        $this
            ->get('/catalog?q=%D0%B5%D0%BB%D0%B5%D0%BA%D1%82%D1%80%D0%B8%D0%BA') // електрик
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.data', 1)
                ->where('offers.data.0.title', 'Послуга 1')
            );
    }

    public function test_catalog_pagination_second_page_shows_remaining_offers_and_meta(): void
    {
        Carbon::setTestNow(now());

        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        // Create 25 offers with deterministic created_at so default `latest()` ordering is stable.
        // Offer 25 is newest; Offer 1 is oldest.
        for ($i = 1; $i <= 25; $i++) {
            Offer::factory()->for($bp)->create([
                'category_id' => $cat->id,
                'title' => 'Offer '.$i,
                'is_active' => true,
                'price_from' => 100,
                'created_at' => now()->subMinutes(25 - $i),
            ]);
        }

        $this
            ->get('/catalog?page=2')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->where('offers.current_page', 2)
                ->where('offers.per_page', 20)
                ->where('offers.total', 25)
                ->has('offers.data', 5)
                ->where('offers.data.0.title', 'Offer 5')
                ->where('offers.data.4.title', 'Offer 1')
            );
    }

    public function test_catalog_pagination_preserves_query_string_in_links(): void
    {
        Carbon::setTestNow(now());

        $cat = Category::factory()->create();
        $bp = BusinessProfile::factory()->create(['city' => 'Київ', 'is_active' => true]);

        for ($i = 1; $i <= 25; $i++) {
            Offer::factory()->for($bp)->create([
                'category_id' => $cat->id,
                'title' => 'Offer '.$i,
                'is_active' => true,
                'price_from' => 100,
                'created_at' => now()->subMinutes(25 - $i),
            ]);
        }

        $this
            ->get('/catalog?q=Offer&sort=price_desc')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Catalog/Index')
                ->has('offers.links')
                ->where('offers.links', function ($links) {
                    $links = $links instanceof \Illuminate\Support\Collection ? $links->all() : (array) $links;

                    // Ensure pagination URLs include original query string params.
                    $urls = array_values(array_filter(array_map(fn ($l) => $l['url'] ?? null, $links)));

                    $hasQ = false;
                    $hasSort = false;
                    $hasPage2 = false;

                    foreach ($urls as $url) {
                        if (str_contains($url, 'q=Offer')) {
                            $hasQ = true;
                        }
                        if (str_contains($url, 'sort=price_desc')) {
                            $hasSort = true;
                        }
                        if (str_contains($url, 'page=2')) {
                            $hasPage2 = true;
                        }
                    }

                    return $hasQ && $hasSort && $hasPage2;
                })
            );
    }

}
