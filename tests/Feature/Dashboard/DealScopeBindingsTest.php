<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealScopeBindingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deal_routes_return_404_when_deal_does_not_belong_to_business_profile(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProfile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
        ]);

        $dealFromOtherProfile = Deal::factory()->create([
            'business_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.deals.show', [$profile, $dealFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.deals.in-progress', [$profile, $dealFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.deals.completed', [$profile, $dealFromOtherProfile]))
            ->assertNotFound();

        $this->actingAs($user)
            ->patch(route('dashboard.deals.cancelled', [$profile, $dealFromOtherProfile]))
            ->assertNotFound();
    }
}
