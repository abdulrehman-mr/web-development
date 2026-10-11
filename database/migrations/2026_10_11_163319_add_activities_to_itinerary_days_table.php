<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itinerary_days', function (Blueprint $table) {
            // [{time, title, description, image}], shown as a timeline under the day.
            $table->json('activities')->nullable()->after('meals');
        });
    }

    public function down(): void
    {
        Schema::table('itinerary_days', function (Blueprint $table) {
            $table->dropColumn('activities');
        });
    }
};
