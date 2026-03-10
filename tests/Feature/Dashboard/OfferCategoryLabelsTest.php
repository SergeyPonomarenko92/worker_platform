<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferCategoryLabelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_create_page_shows_full_category_path_labels(): void
    {
        $user = User::factory()->create();

        $businessProfile = BusinessProfile::factory()->for($user)->create([
            'is_active' => true,
        ]);

        $parent = Category::factory()->create([
            'name' => 'Будівництво',
            'parent_id' => null,
        ]);

        Category::factory()->create([
            'name' => 'Електрика',
            'parent_id' => $parent->id,
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard.offers.create', $businessProfile))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Offers/Create')
                ->has('categories', 2)
                ->where('categories.0.label', 'Будівництво')
                ->where('categories.1.label', 'Будівництво → Електрика')
            );
    }
}
