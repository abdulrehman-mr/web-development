<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_migration_loads_the_full_currency_list(): void
    {
        $this->assertSame(166, Currency::count());
        $this->assertSame('Rs', Currency::find('PKR')->symbol);
        $this->assertSame(3, Currency::find('KWD')->decimals);
    }

    public function test_visitor_can_switch_to_an_enabled_currency(): void
    {
        $package = Package::where('price', 2450)->firstOrFail();

        $this->get('/journeys')->assertSee('$2,450');

        $this->from('/journeys')->post('/currency', ['currency' => 'PKR'])->assertRedirect('/journeys');
        $this->get('/journeys')->assertSee('Rs 690,900')->assertDontSee('$2,450');

        // Disabled currencies are ignored.
        $this->post('/currency', ['currency' => 'JPY']);
        $this->get("/journeys/{$package->slug}")->assertSee('Rs 690,900');
    }

    public function test_booking_records_the_visitors_currency_and_rate(): void
    {
        $package = Package::firstOrFail();
        $this->post('/currency', ['currency' => 'CAD']);

        $this->post("/journeys/{$package->slug}/book", [
            'full_name' => 'Harpreet Kaur', 'email' => 'h@example.ca', 'adults' => 2,
            'start_date' => now()->addMonth()->toDateString(),
        ]);

        $booking = Booking::firstOrFail();
        $this->assertSame('CAD', $booking->currency);
        $this->assertSame(1.38, $booking->exchange_rate);
        $this->get(route('bookings.show', $booking))->assertSee('CA$');
    }

    public function test_admin_can_configure_currencies_and_change_the_base(): void
    {
        $this->actingAs(User::first());
        $this->get('/admin/currencies')->assertOk()->assertSee('All currencies (166)');

        // The form posts only the rows that changed, as one JSON field.
        $changed = [
            'PKR' => ['symbol' => '₨', 'symbol_position' => 'before', 'rate' => 282, 'is_enabled' => 1],
            'INR' => ['symbol' => '₹', 'symbol_position' => 'before', 'rate' => 88, 'is_enabled' => 1],
            'JPY' => ['symbol' => '¥', 'symbol_position' => 'after', 'rate' => null, 'is_enabled' => 1],
        ];

        $this->put('/admin/currencies', [
            'base_currency' => 'CAD', 'default_currency' => 'PKR', 'secondary_currency' => '',
            'currency_switcher' => '1', 'currencies_json' => json_encode($changed),
        ])->assertSessionHasNoErrors();

        $this->assertSame('₨', Currency::find('PKR')->symbol);
        $this->assertTrue(Currency::find('INR')->is_enabled);
        $this->assertFalse(Currency::find('JPY')->is_enabled, 'A currency without a rate cannot be enabled.');
        $this->assertSame('after', Currency::find('JPY')->symbol_position);
        $this->assertSame('€', Currency::find('EUR')->symbol, 'Rows that were not changed are left alone.');
        $this->assertTrue(Currency::find('EUR')->is_enabled);

        // Rates are re-expressed per 1 CAD.
        $this->assertEqualsWithDelta(1.0, Currency::find('CAD')->rate, 0.0001);
        $this->assertEqualsWithDelta(1 / 1.38, Currency::find('USD')->rate, 0.0001);
        $this->assertEqualsWithDelta(282 / 1.38, Currency::find('PKR')->rate, 0.001);
        $this->assertSame('CAD', setting('base_currency'));
    }

    public function test_rates_are_fetched_from_the_provider(): void
    {
        Http::fake(['open.er-api.com/*' => Http::response(['result' => 'success', 'rates' => ['USD' => 1, 'PKR' => 280.5, 'JPY' => 150.2]])]);

        $this->artisan('currencies:update-rates')->assertSuccessful();

        $this->assertSame(280.5, Currency::find('PKR')->rate);
        $this->assertSame(150.2, Currency::find('JPY')->rate);
        Http::assertSent(fn ($request) => $request->url() === 'https://open.er-api.com/v6/latest/USD');
    }

    public function test_rate_provider_errors_are_reported(): void
    {
        Http::fake(['open.er-api.com/*' => Http::response(['result' => 'error', 'error-type' => 'unsupported-code'])]);

        $this->artisan('currencies:update-rates')->assertFailed();
        $this->assertSame(282.0, Currency::find('PKR')->rate);
    }

    public function test_currency_settings_save_without_row_changes(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/currencies', [
            'base_currency' => 'USD', 'default_currency' => 'CAD', 'currencies_json' => '{}',
        ])->assertSessionHasNoErrors();

        $this->assertSame('CAD', setting('default_currency'));
        $this->put('/admin/currencies', ['base_currency' => 'USD', 'default_currency' => 'USD', 'currencies_json' => 'not json'])
            ->assertSessionHasErrors('currencies_json');
    }
}
