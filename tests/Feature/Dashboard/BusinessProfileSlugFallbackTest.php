<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileSlugFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_slug_falls_back_when_slugified_name_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => '🔥',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('profile', $profile->slug);
    }

    public function test_business_profile_slug_fallback_is_unique(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => '🔥',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => '🔥',
            ])
            ->assertRedirect();

        $slugs = BusinessProfile::query()
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->pluck('slug')
            ->all();

        $this->assertSame(['profile', 'profile-2'], $slugs);
    }
}
