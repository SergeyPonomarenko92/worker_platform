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
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('about')->nullable();

            // Ukraine-first, but keep fields flexible for later.
            $table->string('country_code', 2)->default('UA');
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['country_code', 'city']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
