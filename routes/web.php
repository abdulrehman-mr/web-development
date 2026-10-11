<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
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
Route::get('/journeys/{package}/book', [BookingController::class, 'create'])->name('packages.book');
Route::post('/journeys/{package}/book', [BookingController::class, 'store'])->middleware('throttle:6,1')->name('bookings.store');
Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/booking/{booking}/pay', [PaymentController::class, 'show'])->name('bookings.pay');
Route::post('/booking/{booking}/pay', [PaymentController::class, 'start'])->middleware('throttle:10,1')->name('bookings.pay.start');
Route::get('/payments/{payment}/return', [PaymentController::class, 'return'])->name('payments.return');
Route::get('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
Route::post('/webhooks/payments/{gateway}', [PaymentController::class, 'webhook'])->middleware('throttle:120,1')->name('payments.webhook');
Route::post('/currency', [CurrencyController::class, 'update'])->name('currency.update');

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
    Route::post('bookings/{booking}/payment-link', [Admin\BookingController::class, 'sendPaymentLink'])->name('bookings.payment-link');
    Route::get('email', [Admin\EmailSettingsController::class, 'edit'])->name('email.edit');
    Route::put('email', [Admin\EmailSettingsController::class, 'update'])->name('email.update');
    Route::post('email/test', [Admin\EmailSettingsController::class, 'test'])->name('email.test');
    Route::get('email-templates', [Admin\EmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::get('email-templates/{template}/edit', [Admin\EmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::put('email-templates/{template}', [Admin\EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::patch('email-templates/{template}/toggle', [Admin\EmailTemplateController::class, 'toggle'])->name('email-templates.toggle');
    Route::post('email-templates/{template}/preview', [Admin\EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    Route::post('email-templates/{template}/test', [Admin\EmailTemplateController::class, 'test'])->name('email-templates.test');
    Route::post('email-templates/{template}/reset', [Admin\EmailTemplateController::class, 'reset'])->name('email-templates.reset');
    Route::resource('inquiries', Admin\InquiryController::class)->only(['index', 'show', 'destroy']);
    Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
    Route::post('media/scan', [Admin\MediaController::class, 'scan'])->name('media.scan');
    Route::post('media/import', [Admin\MediaController::class, 'import'])->name('media.import');
    Route::delete('media/{medium}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('content-packs', [Admin\ContentPackController::class, 'index'])->name('content-packs.index');
    Route::post('content-packs/{pack}', [Admin\ContentPackController::class, 'import'])->name('content-packs.import');
    Route::delete('content-packs/{pack}', [Admin\ContentPackController::class, 'destroy'])->name('content-packs.destroy');
    Route::get('vibes', [Admin\VibeController::class, 'edit'])->name('vibes.edit');
    Route::put('vibes', [Admin\VibeController::class, 'update'])->name('vibes.update');
    Route::get('room-types', [Admin\RoomTypeController::class, 'edit'])->name('room-types.edit');
    Route::put('room-types', [Admin\RoomTypeController::class, 'update'])->name('room-types.update');
    Route::get('currencies', [Admin\CurrencyController::class, 'index'])->name('currencies.index');
    Route::put('currencies', [Admin\CurrencyController::class, 'update'])->name('currencies.update');
    Route::post('currencies/rates', [Admin\CurrencyController::class, 'refreshRates'])->name('currencies.rates');
    Route::get('appearance', [Admin\AppearanceController::class, 'edit'])->name('appearance.edit');
    Route::put('appearance', [Admin\AppearanceController::class, 'update'])->name('appearance.update');
    Route::get('payments', [Admin\PaymentSettingsController::class, 'edit'])->name('payments.edit');
    Route::put('payments', [Admin\PaymentSettingsController::class, 'update'])->name('payments.update');
    Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::resource('pages', Admin\PageController::class)->except('show');
    Route::post('pages/{page}/sections', [Admin\PageSectionController::class, 'store'])->name('pages.sections.store');
    Route::get('pages/{page}/sections/{section}/edit', [Admin\PageSectionController::class, 'edit'])->scopeBindings()->name('pages.sections.edit');
    Route::put('pages/{page}/sections/{section}', [Admin\PageSectionController::class, 'update'])->scopeBindings()->name('pages.sections.update');
    Route::post('pages/{page}/sections/{section}/duplicate', [Admin\PageSectionController::class, 'duplicate'])->scopeBindings()->name('pages.sections.duplicate');
    Route::patch('pages/{page}/sections/{section}/toggle', [Admin\PageSectionController::class, 'toggle'])->scopeBindings()->name('pages.sections.toggle');
    Route::delete('pages/{page}/sections/{section}', [Admin\PageSectionController::class, 'destroy'])->scopeBindings()->name('pages.sections.destroy');
    Route::put('pages/{page}/order', [Admin\PageSectionController::class, 'reorder'])->name('pages.sections.reorder');
});

// Pages made in Admin → Pages, e.g. /umrah-guide. Kept last so it never hides a built-in route.
Route::get('/{page:slug}', [PageController::class, 'show'])->where('page', '[a-z0-9-]+')->name('pages.show');
