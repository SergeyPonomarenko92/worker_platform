<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileAboutNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profile_about_is_trimmed_and_collapses_spaces_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'about' => "  Про нас\u{00A0}  текст  ",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('Про нас текст', $profile->about);
    }

    public function test_empty_business_profile_about_becomes_null_on_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('dashboard.business-profiles.store'), [
                'name' => 'Test Provider',
                'about' => "\u{00A0}\u{202F}",
            ])
            ->assertRedirect();

        $profile = BusinessProfile::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertNull($profile->about);
    }

    public function test_empty_business_profile_about_becomes_null_on_update(): void
    {
        $user = User::factory()->create();

        $profile = BusinessProfile::factory()->create([
            'user_id' => $user->id,
            'about' => 'Some text',
        ]);

        $this->actingAs($user)
            ->patch(route('dashboard.business-profiles.update', $profile), [
                'name' => $profile->name,
                'about' => '   ',
            ])
            ->assertRedirect();

        $profile->refresh();

        $this->assertNull($profile->about);
    }
}
