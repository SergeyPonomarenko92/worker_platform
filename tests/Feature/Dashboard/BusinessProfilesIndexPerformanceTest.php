<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessProfilesIndexPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_profiles_index_does_not_trigger_n_plus_one_queries(): void
    {
        $user = User::factory()->create();

        // Enough profiles to amplify any accidental per-row loading.
        BusinessProfile::factory()->count(50)->for($user)->create([
            'is_active' => true,
        ]);

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this
            ->actingAs($user)
            ->get('/dashboard/business-profiles')
            ->assertOk();

        // Query budget guard: BusinessProfileController@index should not load extra relations.
        $this->assertLessThanOrEqual(8, $queries);
    }
}
