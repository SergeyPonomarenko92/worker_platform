<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealsIndexInertiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_deals_index_includes_price_fields_for_display(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $businessProfile = BusinessProfile::factory()->for($provider)->create([
            'is_active' => true,
        ]);

        $pricedDeal = Deal::factory()->for($businessProfile)->create([
            'client_user_id' => $client->id,
            'status' => 'draft',
            'agreed_price' => 1500,
            'currency' => 'UAH',
            'created_at' => now()->subMinute(),
        ]);

        $noPriceDeal = Deal::factory()->for($businessProfile)->create([
            'client_user_id' => $client->id,
            'status' => 'draft',
            'agreed_price' => null,
            'currency' => 'UAH',
            'created_at' => now(),
        ]);

        $this
            ->actingAs($provider)
            ->get(route('dashboard.deals.index', $businessProfile))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Deals/Index')
                ->has('deals', 2)
                ->where('deals.0.id', $noPriceDeal->id)
                ->where('deals.0.agreed_price', null)
                ->where('deals.0.currency', 'UAH')
                ->where('deals.1.id', $pricedDeal->id)
                ->where('deals.1.agreed_price', 1500)
                ->where('deals.1.currency', 'UAH')
            );
    }
}
