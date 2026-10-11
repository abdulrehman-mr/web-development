<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('child_price', 10, 2)->nullable()->after('original_price');
            $table->json('group_tiers')->nullable()->after('child_price');
            $table->json('room_options')->nullable()->after('group_tiers');
            $table->unsignedBigInteger('default_room_type_id')->nullable()->after('room_options');
            $table->json('extras')->nullable()->after('default_room_type_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('room_preference')->constrained()->nullOnDelete();
            $table->json('extras')->nullable()->after('room_type_id');
            $table->json('price_breakdown')->nullable()->after('total_estimate');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_type_id');
            $table->dropColumn(['extras', 'price_breakdown']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['child_price', 'group_tiers', 'room_options', 'default_room_type_id', 'extras']);
        });
    }
};
