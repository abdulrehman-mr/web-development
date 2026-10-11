<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Existing content is all in Pakistan. `pack` records which content pack created a row. */
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->char('country', 2)->default('PK')->after('slug')->index();
            $table->string('pack', 40)->nullable()->index();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->char('country', 2)->nullable()->after('category_id')->index();
            $table->string('pack', 40)->nullable()->index();
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->string('pack', 40)->nullable()->index();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('pack', 40)->nullable();
        });

        DB::table('packages')->update(['country' => 'PK']);
    }

    public function down(): void
    {
        Schema::table('destinations', fn (Blueprint $table) => $table->dropColumn(['country', 'pack']));
        Schema::table('packages', fn (Blueprint $table) => $table->dropColumn(['country', 'pack']));
        Schema::table('hotels', fn (Blueprint $table) => $table->dropColumn('pack'));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('pack'));
    }
};
