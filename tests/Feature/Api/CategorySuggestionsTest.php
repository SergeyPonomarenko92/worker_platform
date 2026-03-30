<?php

namespace Tests\Feature\Api;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_empty_array_when_query_is_too_short(): void
    {
        $this->getJson(route('api.categories', ['q' => 'р']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public')
            ->assertExactJson([]);
    }

    public function test_suggests_only_categories_that_have_active_offers_and_returns_category_path(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create([
            'user_id' => $provider->id,
            'is_active' => true,
        ]);

        $parent = Category::factory()->create(['name' => 'Будівництво', 'slug' => 'budivnytstvo']);
        $child = Category::factory()->create(['parent_id' => $parent->id, 'name' => 'Ремонт', 'slug' => 'remont']);
        $grandchild = Category::factory()->create(['parent_id' => $child->id, 'name' => 'Ремонт квартир', 'slug' => 'remont-kvartyr']);

        // Visible offer in grandchild.
        Offer::factory()->create([
            'business_profile_id' => $profile->id,
            'category_id' => $grandchild->id,
            'is_active' => true,
        ]);

        // Category with only inactive offers should not be suggested.
        $inactiveOnly = Category::factory()->create(['name' => 'Ремонт авто', 'slug' => 'remont-auto']);
        Offer::factory()->create([
            'business_profile_id' => $profile->id,
            'category_id' => $inactiveOnly->id,
            'is_active' => false,
        ]);

        $resp = $this->getJson(route('api.categories', ['q' => 'рем']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public');

        $json = $resp->json();

        $this->assertIsArray($json);
        $this->assertCount(1, $json);

        $this->assertSame($grandchild->id, $json[0]['id']);
        $this->assertSame('Ремонт квартир', $json[0]['name']);
        $this->assertSame('remont-kvartyr', $json[0]['slug']);
        $this->assertSame('Будівництво → Ремонт → Ремонт квартир', $json[0]['path']);
    }
}
