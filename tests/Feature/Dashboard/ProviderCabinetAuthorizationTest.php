<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderCabinetAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_edit_someone_elses_business_profile(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.business-profile.edit'))
            ->assertRedirect(route('dashboard.business-profile.create'));

        // Intruder creates their own profile instead; can't patch owner's profile.
        $this->actingAs($intruder)
            ->patch(route('dashboard.business-profile.update'), [
                'name' => 'Hacked',
            ])
            ->assertNotFound();
    }

    public function test_user_cannot_edit_or_delete_someone_elses_offer(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $owner->id,
        ]);

        $offer = Offer::factory()->create([
            'business_profile_id' => $profile->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('dashboard.offers.edit', $offer))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('dashboard.offers.update', $offer), [
                'type' => 'service',
                'title' => 'Nope',
                'currency' => 'UAH',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('dashboard.offers.destroy', $offer))
            ->assertForbidden();
    }
}
