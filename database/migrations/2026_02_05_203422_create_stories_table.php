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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();

            // MVP: store path (later: separate media table + CDN)
            $table->string('media_path');
            $table->text('caption')->nullable();

            $table->timestamp('expires_at');

            $table->timestamps();

            $table->index(['business_profile_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
