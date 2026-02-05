<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portfolio_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('body')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index(['business_profile_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_posts');
    }
};
