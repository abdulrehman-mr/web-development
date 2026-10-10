<?php

use Database\Seeders\CurrencySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->char('code', 3)->primary();
            $table->string('name');
            $table->string('symbol', 12);
            $table->string('symbol_position', 6)->default('before');
            $table->unsignedTinyInteger('decimals')->default(2);
            $table->decimal('rate', 20, 8)->nullable()->comment('Units of this currency per 1 unit of the base currency');
            $table->boolean('is_enabled')->default(false)->index();
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
        });

        // Load all ISO currencies so existing installs get them on `php artisan migrate`.
        (new CurrencySeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
