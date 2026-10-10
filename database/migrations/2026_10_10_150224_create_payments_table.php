<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('gateway', 30);
            $table->string('gateway_reference')->nullable()->index();
            $table->string('type', 10)->default('deposit');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->decimal('base_amount', 14, 2);
            $table->string('status', 20)->default('pending')->index();
            $table->string('failure_reason', 500)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
