<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealStatusTransitionGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_mark_in_progress_when_deal_is_completed(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.show', [$profile, $deal]))
            ->patch(route('dashboard.deals.in-progress', [$profile, $deal]))
            ->assertRedirect(route('dashboard.deals.show', [$profile, $deal]))
            ->assertSessionHas('error', 'Неможливо змінити статус: угода вже завершена або скасована.');

        $deal->refresh();
        $this->assertSame('completed', $deal->status);
        $this->assertNotNull($deal->completed_at);
    }

    public function test_cannot_mark_in_progress_when_deal_is_cancelled(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.show', [$profile, $deal]))
            ->patch(route('dashboard.deals.in-progress', [$profile, $deal]))
            ->assertRedirect(route('dashboard.deals.show', [$profile, $deal]))
            ->assertSessionHas('error', 'Неможливо змінити статус: угода вже завершена або скасована.');

        $deal->refresh();
        $this->assertSame('cancelled', $deal->status);
    }

    public function test_cannot_cancel_completed_deal(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'status' => 'completed',
            'completed_at' => now()->subHour(),
        ]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.show', [$profile, $deal]))
            ->patch(route('dashboard.deals.cancelled', [$profile, $deal]))
            ->assertRedirect(route('dashboard.deals.show', [$profile, $deal]))
            ->assertSessionHas('error', 'Неможливо скасувати завершену угоду.');

        $deal->refresh();
        $this->assertSame('completed', $deal->status);
    }

    public function test_cannot_complete_cancelled_deal(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.show', [$profile, $deal]))
            ->patch(route('dashboard.deals.completed', [$profile, $deal]))
            ->assertRedirect(route('dashboard.deals.show', [$profile, $deal]))
            ->assertSessionHas('error', 'Неможливо завершити скасовану угоду.');

        $deal->refresh();
        $this->assertSame('cancelled', $deal->status);
        $this->assertNull($deal->completed_at);
    }

    public function test_mark_completed_is_idempotent(): void
    {
        $provider = User::factory()->create();
        $profile = BusinessProfile::factory()->create(['user_id' => $provider->id]);

        $completedAt = now()->subMinutes(10);

        $deal = Deal::factory()->create([
            'business_profile_id' => $profile->id,
            'status' => 'completed',
            'completed_at' => $completedAt,
        ]);

        $this->actingAs($provider)
            ->from(route('dashboard.deals.show', [$profile, $deal]))
            ->patch(route('dashboard.deals.completed', [$profile, $deal]))
            ->assertRedirect(route('dashboard.deals.show', [$profile, $deal]))
            ->assertSessionHas('success', 'Угоду вже завершено.');

        $deal->refresh();
        $this->assertSame('completed', $deal->status);
        $this->assertNotNull($deal->completed_at);
        $this->assertSame($completedAt->format('Y-m-d H:i:s'), $deal->completed_at->format('Y-m-d H:i:s'));
    }
}
