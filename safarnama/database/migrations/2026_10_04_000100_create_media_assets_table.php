<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->char('source_hash', 40)->unique();
            $table->text('source_url');
            $table->string('path')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('error', 500)->nullable();
            $table->string('mime')->nullable();
            $table->unsignedInteger('bytes')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
