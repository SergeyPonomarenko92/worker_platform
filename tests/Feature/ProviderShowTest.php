<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_page_includes_offer_category(): void
    {
        $category = Category::factory()->create(['name' => 'Електрика']);
        $provider = BusinessProfile::factory()->create(['slug' => 'demo-provider']);

        Offer::factory()->for($provider)->create([
            'category_id' => $category->id,
            'title' => 'Demo offer',
            'is_active' => true,
        ]);

        $this
            ->get('/providers/'.$provider->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Providers/Show')
                ->has('provider.offers', 1)
                ->where('provider.offers.0.title', 'Demo offer')
                ->where('provider.offers.0.category.name', 'Електрика')
            );
    }
}
