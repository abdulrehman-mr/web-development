<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Departure;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepartureTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->package = Package::where('title', 'Footsteps of Guru Nanak: Sikh Yatra')->firstOrFail();
        $this->package->update(['price' => 2000, 'group_tiers' => null, 'room_options' => null, 'extras' => null, 'date_mode' => 'departures']);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = [])
    {
        return $this->post("/journeys/{$this->package->slug}/book", $overrides + [
            'full_name' => 'Harpreet Kaur', 'email' => 'h@example.ca', 'country' => 'Canada', 'adults' => 2, 'children' => 0,
        ]);
    }

    private function departure(array $attributes = []): Departure
    {
        return Departure::factory()->create($attributes + ['package_id' => $this->package->id]);
    }

    public function test_guest_joins_a_departure_and_gets_its_date_and_price_change(): void
    {
        $departure = $this->departure(['starts_on' => now()->addMonths(2)->startOfDay(), 'price_adjustment' => 150, 'label' => 'Gurpurab batch']);

        $this->book(['departure_id' => $departure->id, 'start_date' => now()->addYear()->toDateString()])->assertRedirect();

        $booking = Booking::latest('id')->first();
        $this->assertSame($departure->id, $booking->departure_id);
        $this->assertSame($departure->starts_on->toDateString(), $booking->start_date->toDateString());
        $this->assertEquals(2 * 2000 + 2 * 150, $booking->total_estimate);
    }

    public function test_departure_without_enough_seats_is_rejected(): void
    {
        $departure = $this->departure(['seats' => 4]);
        $this->package->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 3, 'start_date' => $departure->starts_on, 'departure_id' => $departure->id]);

        $this->book(['departure_id' => $departure->id])->assertSessionHasErrors(['departure_id' => 'Only 1 seat left on that departure.']);
        $this->book(['departure_id' => $departure->id, 'adults' => 1])->assertSessionHasNoErrors();
        $this->book(['departure_id' => $departure->id, 'adults' => 1])->assertSessionHasErrors(['departure_id' => 'That departure is full. Please choose another.']);
    }

    public function test_cancelled_bookings_free_their_seats(): void
    {
        $departure = $this->departure(['seats' => 2]);
        $this->package->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 2, 'start_date' => $departure->starts_on, 'departure_id' => $departure->id, 'status' => 'cancelled']);

        $this->assertSame(2, $departure->seatsLeft());
        $this->book(['departure_id' => $departure->id])->assertSessionHasNoErrors();
    }

    public function test_fixed_departure_journey_requires_an_open_departure_of_that_journey(): void
    {
        $closed = $this->departure(['is_closed' => true]);
        $past = $this->departure(['starts_on' => now()->subDay()]);
        $otherJourney = Departure::factory()->create(['package_id' => Package::where('id', '!=', $this->package->id)->value('id')]);
        $this->departure();

        $this->book(['start_date' => now()->addMonth()->toDateString()])->assertSessionHasErrors(['departure_id' => 'Please choose one of the departures.']);
        foreach ([$closed, $past, $otherJourney] as $departure) {
            $this->book(['departure_id' => $departure->id])->assertSessionHasErrors(['departure_id' => 'That departure is no longer available. Please choose another.']);
        }
        $this->assertSame(0, Booking::count());
    }

    public function test_journey_with_both_options_accepts_the_guests_own_date(): void
    {
        $this->package->update(['date_mode' => 'both']);
        $this->departure();

        $this->book(['departure_id' => '', 'start_date' => now()->addMonths(3)->toDateString()])->assertRedirect();
        $this->assertNull(Booking::latest('id')->first()->departure_id);
    }

    public function test_fixed_departure_journey_with_no_dates_scheduled_falls_back_to_any_date(): void
    {
        $this->book(['start_date' => now()->addMonth()->toDateString()])->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_journey_and_booking_pages_show_seats_left_and_sold_out(): void
    {
        $open = $this->departure(['starts_on' => now()->addMonths(2)->startOfDay(), 'seats' => 10, 'label' => 'Vaisakhi batch']);
        $full = $this->departure(['starts_on' => now()->addMonths(3)->startOfDay(), 'seats' => 2]);
        $this->package->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 2, 'start_date' => $full->starts_on, 'departure_id' => $full->id]);

        $this->get("/journeys/{$this->package->slug}")->assertSee('Next departures')->assertSee('10 seats left')->assertSee('Sold out');
        $this->get("/journeys/{$this->package->slug}/book?departure={$open->id}")->assertSee('Vaisakhi batch')->assertSee('Sold out')
            ->assertDontSee('id="start_date"', false);
    }

    public function test_admin_saves_departures_and_closes_removed_ones_that_have_bookings(): void
    {
        $this->actingAs(User::first());
        $booked = $this->departure(['starts_on' => now()->addMonth()->startOfDay()]);
        $unbooked = $this->departure(['starts_on' => now()->addMonths(2)->startOfDay()]);
        $this->package->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 1, 'start_date' => $booked->starts_on, 'departure_id' => $booked->id]);

        $this->put("/admin/packages/{$this->package->slug}", [
            'title' => $this->package->title, 'duration_days' => 9, 'price' => 2000, 'difficulty' => 'Easy', 'is_published' => '1',
            'date_mode' => 'both',
            'departures' => [['id' => '', 'starts_on' => now()->addMonths(4)->toDateString(), 'seats' => '', 'price_adjustment' => '-100', 'label' => 'Winter', 'is_closed' => '0']],
        ])->assertSessionHasNoErrors();

        $this->assertSame('both', $this->package->fresh()->date_mode);
        $this->assertTrue($booked->fresh()->is_closed);
        $this->assertNull($unbooked->fresh());
        $new = $this->package->departures()->where('label', 'Winter')->first();
        $this->assertNull($new->seats);
        $this->assertSame(-100.0, $new->price_adjustment);
    }

    public function test_admin_adds_timed_activities_with_photos_to_a_day(): void
    {
        Storage::fake('public');
        $this->actingAs(User::first());

        $this->put("/admin/packages/{$this->package->slug}", [
            'title' => $this->package->title, 'duration_days' => 9, 'price' => 2000, 'difficulty' => 'Easy', 'is_published' => '1',
            'days' => [['title' => 'Lahore', 'activities' => [
                ['time' => 'Morning · 10 am', 'title' => 'Badshahi <b>Mosque</b>', 'description' => 'Mughal grandeur', 'image_file' => UploadedFile::fake()->image('mosque.jpg')],
                ['time' => 'Noon · 1 pm', 'title' => 'Food Street', 'description' => '', 'image' => 'https://example.com/food.jpg'],
                ['time' => '', 'title' => '', 'description' => '', 'image' => ''],
            ]]],
        ])->assertSessionHasNoErrors();

        $activities = $this->package->fresh()->days->first()->activities;
        $this->assertCount(2, $activities);
        Storage::disk('public')->assertExists($activities[0]['image']);
        $this->assertSame('https://example.com/food.jpg', $activities[1]['image']);

        $this->get("/journeys/{$this->package->slug}")->assertSee('Morning · 10 am')->assertSee('Badshahi &lt;b&gt;Mosque&lt;/b&gt;', false)->assertSee('https://example.com/food.jpg');
    }
}
