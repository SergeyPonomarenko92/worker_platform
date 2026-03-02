<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileCountryCodeNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_country_code_is_uppercased_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'country_code' => '  ua ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('UA', $profile->country_code);
    }

    public function test_empty_business_profile_country_code_falls_back_to_ua_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'country_code' => "\u{00A0}\u{202F}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('UA', $profile->country_code);
    }

    public function test_business_profile_country_code_is_uppercased_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'country_code' => 'UA',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'country_code' => ' pl ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('PL', $profile->country_code);
    }

    public function test_empty_business_profile_country_code_falls_back_to_ua_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'country_code' => 'PL',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'country_code' => '   ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('UA', $profile->country_code);
    }
}
