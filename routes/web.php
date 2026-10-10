<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// ── Public site ─────────────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');
Route::get('/journeys', [PackageController::class, 'index'])->name('packages.index');
Route::get('/journeys/{package}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/stays', [PageController::class, 'stays'])->name('stays');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])->middleware('throttle:6,1')->name('contact.send');
Route::post('/journeys/{package}/book', [BookingController::class, 'store'])->middleware('throttle:6,1')->name('bookings.store');
Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');

// ── Admin auth ──────────────────────────────────────────────────────
Route::get('/admin/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/admin/login', [LoginController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ── Admin panel ─────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::resource('packages', Admin\PackageController::class)->except('show');
    Route::resource('destinations', Admin\DestinationController::class)->except('show');
    Route::resource('hotels', Admin\HotelController::class)->except('show');
    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
    Route::resource('faqs', Admin\FaqController::class)->except('show');
    Route::resource('bookings', Admin\BookingController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::resource('inquiries', Admin\InquiryController::class)->only(['index', 'show', 'destroy']);
    Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
    Route::post('media/scan', [Admin\MediaController::class, 'scan'])->name('media.scan');
    Route::post('media/import', [Admin\MediaController::class, 'import'])->name('media.import');
    Route::delete('media/{medium}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});
