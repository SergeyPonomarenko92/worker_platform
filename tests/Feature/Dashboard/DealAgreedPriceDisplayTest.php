<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealAgreedPriceDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_deal_show_page_receives_agreed_price_zero_value(): void
    {
        $provider = User::factory()->create();
        $client = User::factory()->create(['email' => 'client@example.com']);

        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'client_user_id' => $client->id,
            'currency' => 'UAH',
            'agreed_price' => 0,
        ]);

        $this
            ->actingAs($provider)
            ->get(route('dashboard.deals.show', [$profile, $deal]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Deals/Show')
                ->where('deal.id', $deal->id)
                ->where('deal.currency', 'UAH')
                ->where('deal.agreed_price', 0)
            );
    }
}
