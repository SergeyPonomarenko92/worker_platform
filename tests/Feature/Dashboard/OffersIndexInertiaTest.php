<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffersIndexInertiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_index_includes_is_active_flag_for_badges(): void
    {
        $user = User::factory()->create();

        $businessProfile = BusinessProfile::factory()->for($user)->create([
            'is_active' => true,
        ]);

        $activeOffer = Offer::factory()->for($businessProfile)->create([
            'title' => 'Active offer',
            'is_active' => true,
        ]);

        $inactiveOffer = Offer::factory()->for($businessProfile)->create([
            'title' => 'Inactive offer',
            'is_active' => false,
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard.offers.index', $businessProfile))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Offers/Index')
                ->has('offers', 2)
                ->where('offers.0.id', $inactiveOffer->id)
                ->where('offers.0.is_active', false)
                ->where('offers.1.id', $activeOffer->id)
                ->where('offers.1.is_active', true)
            );
    }
}
