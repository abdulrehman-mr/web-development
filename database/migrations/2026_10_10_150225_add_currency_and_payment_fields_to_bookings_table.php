<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->char('currency', 3)->nullable()->after('total_estimate')->comment('Currency the guest was browsing in');
            $table->decimal('exchange_rate', 20, 8)->default(1)->after('currency');
            $table->decimal('amount_paid', 14, 2)->default(0)->after('exchange_rate')->comment('In the base currency');
            $table->string('payment_status', 20)->default('unpaid')->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'amount_paid', 'payment_status']);
        });
    }
};
