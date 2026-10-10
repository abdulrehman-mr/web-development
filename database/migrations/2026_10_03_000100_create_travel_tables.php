<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('highlights')->nullable();
            $table->string('best_season')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('Boutique hotel');
            $table->unsignedTinyInteger('stars')->default(4);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('amenities')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 500)->nullable();
            $table->text('overview')->nullable();
            $table->unsignedSmallInteger('duration_days')->default(7);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('group_size')->nullable();
            $table->string('difficulty')->default('Easy');
            $table->string('best_season')->nullable();
            $table->string('start_city')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('highlights')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('destination_package', function (Blueprint $table) {
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->primary(['destination_id', 'package_id']);
        });

        Schema::create('itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('meals')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->date('start_date')->nullable();
            $table->string('room_preference')->nullable();
            $table->text('special_requests')->nullable();
            $table->decimal('total_estimate', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'faqs', 'testimonials', 'inquiries', 'bookings', 'itinerary_days', 'destination_package', 'packages', 'hotels', 'destinations', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
