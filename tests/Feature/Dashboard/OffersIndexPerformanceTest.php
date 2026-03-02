<?php

namespace Tests\Feature\Dashboard;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OffersIndexPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_index_does_not_trigger_n_plus_one_queries(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Електрика']);

        $businessProfile = BusinessProfile::factory()->for($user)->create([
            'is_active' => true,
        ]);

        // Enough offers to amplify any accidental per-row loading.
        Offer::factory()->count(50)->for($businessProfile)->create([
            'category_id' => $category->id,
            'is_active' => true,
            'price_from' => 100,
        ]);

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this
            ->actingAs($user)
            ->get('/dashboard/business-profiles/'.$businessProfile->id.'/offers')
            ->assertOk();

        // Query budget guard: OfferController@index should eager-load categories.
        $this->assertLessThanOrEqual(10, $queries);
    }
}
