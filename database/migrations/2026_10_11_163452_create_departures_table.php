<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A fixed group departure ("batch") of a journey that guests can join.
        Schema::create('departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->unsignedSmallInteger('seats')->nullable();
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->string('label', 120)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->index(['package_id', 'starts_on']);
        });

        Schema::table('packages', function (Blueprint $table) {
            // any = guest picks any date; departures = fixed batches only; both = batches or own date.
            $table->string('date_mode', 20)->default('any')->after('option_groups');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('departure_id')->nullable()->after('package_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departure_id');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('date_mode');
        });
        Schema::dropIfExists('departures');
    }
};
