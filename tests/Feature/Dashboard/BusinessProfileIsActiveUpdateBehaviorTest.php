<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileIsActiveUpdateBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_is_active_field_does_not_overwrite_existing_value_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                // Intentionally omit is_active to mimic unchecked checkbox not being sent.
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertFalse($profile->is_active);
    }

    public function test_is_active_can_be_updated_when_present_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertFalse($profile->is_active);
    }
}
