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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->nullable()->constrained()->nullOnDelete();

            // draft|in_progress|completed|cancelled
            $table->string('status', 30)->default('draft');

            $table->unsignedInteger('agreed_price')->nullable();
            $table->string('currency', 3)->default('UAH');

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['business_profile_id', 'status']);
            $table->index(['client_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
