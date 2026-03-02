<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Provider public page frequently queries these tables by (business_profile_id + time).
        // Add composite indexes to keep lookups bounded as data grows.

        Schema::table('stories', function (Blueprint $table) {
            $table->index(['business_profile_id', 'expires_at'], 'stories_bp_expires_at_index');
        });

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->index(['business_profile_id', 'published_at'], 'portfolio_posts_bp_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex('stories_bp_expires_at_index');
        });

        Schema::table('portfolio_posts', function (Blueprint $table) {
            $table->dropIndex('portfolio_posts_bp_published_at_index');
        });
    }
};
