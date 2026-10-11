<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_public_pages_render(): void
    {
        $package = Package::first();
        $destination = Destination::first();

        foreach (['/', '/journeys', '/journeys?category=sikh-yatra&duration=medium&sort=price_asc', "/journeys/{$package->slug}",
            '/destinations', "/destinations/{$destination->slug}", '/stays', '/about', '/contact'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/')->assertSee('Footsteps of Guru Nanak');
        $this->get("/journeys/{$package->slug}")->assertSee($package->days->first()->title)->assertSee('Where you’ll stay', false);
    }

    public function test_unpublished_journey_is_hidden(): void
    {
        $package = Package::first();
        $package->update(['is_published' => false]);

        $this->get("/journeys/{$package->slug}")->assertNotFound();
        $this->get('/journeys')->assertDontSee($package->title);
    }

    public function test_visitor_can_request_a_booking(): void
    {
        $package = Package::where('title', 'Cholistan: Palaces & Desert Forts')->firstOrFail();
        $this->get("/journeys/{$package->slug}/book")->assertOk()->assertSee('Choose your room');

        $response = $this->post("/journeys/{$package->slug}/book", [
            'full_name' => 'Harpreet Kaur',
            'email' => 'harpreet@example.ca',
            'country' => 'Canada',
            'adults' => 2,
            'children' => 1,
            'start_date' => now()->addMonths(2)->toDateString(),
        ]);

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertSame('pending', $booking->status);
        $this->assertEquals(round($package->price * 2.7, 2), (float) $booking->total_estimate);

        $this->get(route('bookings.show', $booking))->assertOk()->assertSee($booking->reference);
    }

    public function test_booking_confirmation_is_private_to_the_guest(): void
    {
        $booking = Package::first()->bookings()->create([
            'full_name' => 'A', 'email' => 'a@example.com', 'adults' => 1, 'start_date' => now()->addMonth(),
        ]);

        $this->get(route('bookings.show', $booking))->assertNotFound();
    }

    public function test_booking_validation(): void
    {
        $package = Package::first();
        $this->post("/journeys/{$package->slug}/book", ['adults' => 0, 'start_date' => '2000-01-01'])
            ->assertSessionHasErrors(['full_name', 'email', 'adults', 'start_date']);
        $this->assertSame(0, Booking::count());
    }

    public function test_contact_form_creates_an_inquiry(): void
    {
        $this->post('/contact', ['name' => 'Daniel', 'email' => 'd@example.com', 'message' => 'Hunza in May?'])
            ->assertSessionHas('status');
        $this->assertSame(1, Inquiry::count());

        // Honeypot submissions are silently dropped.
        $this->post('/contact', ['name' => 'Bot', 'email' => 'b@example.com', 'message' => 'spam', 'website' => 'x']);
        $this->assertSame(1, Inquiry::count());
    }

    public function test_admin_requires_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();

        $this->post('/admin/login', ['email' => 'admin@safarnama.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'admin@safarnama.test', 'password' => 'password'])->assertRedirect('/admin');
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs(User::first());
        $booking = Package::first()->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 1, 'start_date' => now()->addMonth()]);
        $inquiry = Inquiry::create(['name' => 'B', 'email' => 'b@example.com', 'message' => 'Hi']);

        foreach (['/admin', '/admin/packages', '/admin/packages/create', '/admin/packages/'.Package::first()->slug.'/edit',
            '/admin/destinations', '/admin/destinations/create', '/admin/destinations/'.Destination::first()->slug.'/edit',
            '/admin/hotels', '/admin/hotels/create', '/admin/hotels/'.Hotel::first()->id.'/edit',
            '/admin/categories', '/admin/categories/create', '/admin/testimonials', '/admin/testimonials/create',
            '/admin/faqs', '/admin/faqs/create', '/admin/bookings', '/admin/bookings?status=pending',
            "/admin/bookings/{$booking->reference}", '/admin/inquiries', "/admin/inquiries/{$inquiry->id}", '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->assertTrue($inquiry->fresh()->is_read);
    }

    public function test_admin_can_create_a_journey_with_itinerary_and_stays(): void
    {
        Storage::fake('public');
        $this->actingAs(User::first());
        $hotel = Hotel::first();
        $destination = Destination::first();

        $this->post('/admin/packages', [
            'title' => 'Swat Valley Escape',
            'duration_days' => 4,
            'price' => 990,
            'difficulty' => 'Easy',
            'is_published' => '1',
            'highlights' => "Malam Jabba\n\nKalam forest",
            'destination_ids' => [$destination->id],
            'hero_image_file' => UploadedFile::fake()->image('swat.jpg', 1600, 900),
            'days' => [
                ['title' => 'Islamabad to Swat', 'description' => 'Drive north.', 'hotel_id' => $hotel->id, 'meals' => 'Dinner'],
                ['title' => 'Kalam', 'description' => null, 'hotel_id' => '', 'meals' => ''],
            ],
        ])->assertRedirect();

        $package = Package::where('slug', 'swat-valley-escape')->firstOrFail();
        $this->assertSame(['Malam Jabba', 'Kalam forest'], $package->highlights);
        $this->assertCount(2, $package->days);
        $this->assertSame($hotel->id, $package->days->first()->hotel_id);
        $this->assertSame(2, $package->days->last()->day_number);
        Storage::disk('public')->assertExists($package->hero_image);

        $this->get('/journeys/swat-valley-escape')->assertOk()->assertSee('Islamabad to Swat')->assertSee($hotel->name);

        // Editing replaces the itinerary.
        $this->put("/admin/packages/{$package->slug}", [
            'title' => 'Swat Valley Escape', 'duration_days' => 3, 'price' => 950, 'difficulty' => 'Easy', 'is_published' => '1',
            'days' => [['title' => 'Only day']],
        ])->assertRedirect();
        $this->assertSame(['Only day'], $package->fresh()->days->pluck('title')->all());
    }

    public function test_admin_can_update_site_content_and_bookings(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/settings', ['hero_title' => 'Discover Pakistan with us'])->assertSessionHas('status');
        $this->get('/')->assertSee('Discover Pakistan with us');

        $booking = Package::first()->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 1, 'start_date' => now()->addMonth()]);
        $this->put("/admin/bookings/{$booking->reference}", ['status' => 'confirmed', 'admin_notes' => 'Deposit received'])->assertSessionHas('status');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_json_api(): void
    {
        $package = Package::first();

        $this->getJson('/api/v1/packages')->assertOk()->assertJsonStructure(['data' => [['title', 'slug', 'price', 'image']], 'total', 'current_page']);
        $this->getJson("/api/v1/packages/{$package->slug}")->assertOk()
            ->assertJsonPath('data.slug', $package->slug)
            ->assertJsonStructure(['data' => ['itinerary' => [['day', 'title', 'stay']]]]);
        $this->getJson('/api/v1/destinations')->assertOk();
        $this->getJson('/api/v1/settings')->assertOk()->assertJsonPath('data.site_name', 'Safarnama');
        $this->getJson('/api/v1/packages/does-not-exist')->assertNotFound();

        $this->postJson('/api/v1/bookings', [
            'package' => $package->slug, 'full_name' => 'API Guest', 'email' => 'api@example.com',
            'adults' => 2, 'start_date' => now()->addMonths(3)->toDateString(),
        ])->assertCreated()->assertJsonPath('data.status', 'pending');

        $this->postJson('/api/v1/bookings', ['package' => 'nope'])->assertUnprocessable();
        $this->postJson('/api/v1/inquiries', ['name' => 'X', 'email' => 'x@example.com', 'message' => 'Hello'])->assertCreated();
    }

    public function test_seeding_again_keeps_admin_password_and_site_content(): void
    {
        User::first()->update(['password' => bcrypt('my-new-password')]);
        $this->actingAs(User::first())->put('/admin/settings', ['hero_title' => 'Our own headline']);
        $packages = Package::count();

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('my-new-password', User::first()->password));
        $this->assertSame('Our own headline', setting('hero_title'));
        $this->assertSame($packages, Package::count());
    }
}
