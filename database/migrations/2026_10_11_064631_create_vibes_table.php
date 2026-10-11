<?php

use Database\Seeders\VibeSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vibes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon', 30)->default('sparkle');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('package_vibe', function (Blueprint $table) {
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vibe_id')->constrained()->cascadeOnDelete();
            $table->primary(['package_id', 'vibe_id']);
        });

        (new VibeSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('package_vibe');
        Schema::dropIfExists('vibes');
    }
};
