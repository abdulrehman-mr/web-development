<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Option groups are per-journey choices such as "Hotel in Makkah" or "Package class",
     * each option adding or removing a price. Pack room types are only offered on
     * journeys that list them explicitly.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('option_groups')->nullable()->after('extras');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->json('options')->nullable()->after('extras');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->string('pack', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('packages', fn (Blueprint $table) => $table->dropColumn('option_groups'));
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('options'));
        Schema::table('room_types', fn (Blueprint $table) => $table->dropColumn('pack'));
    }
};
