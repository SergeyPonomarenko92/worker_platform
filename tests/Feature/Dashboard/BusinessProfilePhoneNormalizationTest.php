<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfilePhoneNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_phone_is_trimmed_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'phone' => '  +380 99 123 45 67  ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('+380 99 123 45 67', $profile->phone);
    }

    public function test_business_profile_phone_normalizes_unicode_spaces_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'phone' => "\u{00A0}+380\u{202F}99\u{00A0}123\u{202F}45\u{00A0}67\u{00A0}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('+380 99 123 45 67', $profile->phone);
    }

    public function test_empty_business_profile_phone_becomes_null_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'phone' => '   ',
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertNull($profile->phone);
    }

    public function test_business_profile_phone_is_trimmed_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'phone' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'phone' => ' 050 123 45 67 ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame('050 123 45 67', $profile->phone);
    }

    public function test_empty_business_profile_phone_becomes_null_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'phone' => '+380501234567',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'phone' => '   ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertNull($profile->phone);
    }
}
