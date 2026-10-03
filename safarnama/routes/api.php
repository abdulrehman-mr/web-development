<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Support\Facades\Route;

// Public, read-only JSON API for the catalogue plus booking/enquiry submission.
Route::prefix('v1')->group(function () {
    Route::get('settings', [CatalogController::class, 'settings']);
    Route::get('categories', [CatalogController::class, 'categories']);
    Route::get('destinations', [CatalogController::class, 'destinations']);
    Route::get('destinations/{destination}', [CatalogController::class, 'destination']);
    Route::get('packages', [CatalogController::class, 'packages']);
    Route::get('packages/{package}', [CatalogController::class, 'package']);
    Route::get('hotels', [CatalogController::class, 'hotels']);
    Route::get('testimonials', [CatalogController::class, 'testimonials']);
    Route::get('faqs', [CatalogController::class, 'faqs']);

    Route::middleware('throttle:6,1')->group(function () {
        Route::post('bookings', [SubmissionController::class, 'booking']);
        Route::post('inquiries', [SubmissionController::class, 'inquiry']);
    });
});
