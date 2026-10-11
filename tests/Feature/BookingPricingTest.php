<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPricingTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->package = Package::where('title', 'Footsteps of Guru Nanak: Sikh Yatra')->firstOrFail();
        $this->package->update([
            'price' => 2000,
            'child_price' => null,
            'group_tiers' => [['min' => 6, 'price' => 1800], ['min' => 10, 'price' => 1700]],
            'room_options' => null,
            'extras' => [
                ['name' => 'SIM card', 'description' => null, 'price' => 15, 'per' => 'person'],
                ['name' => 'Photographer', 'description' => null, 'price' => 250, 'per' => 'booking'],
            ],
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function book(array $overrides = [])
    {
        return $this->post("/journeys/{$this->package->slug}/book", $overrides + [
            'full_name' => 'Harpreet Kaur', 'email' => 'h@example.ca', 'country' => 'Canada',
            'adults' => 2, 'children' => 0, 'start_date' => now()->addMonth()->toDateString(),
        ]);
    }

    public function test_server_prices_adults_children_room_and_extras(): void
    {
        $single = RoomType::where('name', 'Single room')->firstOrFail(); // +450 per adult

        $this->book(['adults' => 2, 'children' => 1, 'room_type_id' => $single->id, 'extras' => ['0', '1'], 'total_estimate' => 1])
            ->assertSessionHasNoErrors();

        $booking = Booking::firstOrFail();
        // 2 × 2000 + 1 × 1400 (70%) + 2 × 450 + 3 × 15 + 250
        $this->assertEquals(6595, (float) $booking->total_estimate);
        $this->assertSame('Single room', $booking->room_preference);
        $this->assertSame(['SIM card', 'Photographer'], array_column($booking->extras, 'name'));
        $this->assertCount(5, $booking->price_breakdown);
    }

    public function test_group_price_applies_from_the_tier_size(): void
    {
        $this->book(['adults' => 5, 'children' => 0]);
        $this->assertEquals(5 * 2000, (float) Booking::latest('id')->first()->total_estimate);

        $this->book(['adults' => 4, 'children' => 2]);
        // 6 travellers: adults 1800, children 70% of the group price
        $this->assertEquals(4 * 1800 + 2 * 1260, (float) Booking::latest('id')->first()->total_estimate);

        $this->book(['adults' => 10]);
        $this->assertEquals(10 * 1700, (float) Booking::latest('id')->first()->total_estimate);
    }

    public function test_fixed_child_price_and_room_discount(): void
    {
        $triple = RoomType::where('name', 'Triple sharing')->firstOrFail(); // −90 per adult
        $this->package->update(['child_price' => 900]);

        $this->book(['adults' => 3, 'children' => 2, 'room_type_id' => $triple->id]);

        $this->assertEquals(3 * 2000 + 2 * 900 - 3 * 90, (float) Booking::firstOrFail()->total_estimate);
    }

    public function test_journey_can_disable_rooms_and_override_prices(): void
    {
        $twin = RoomType::where('name', 'Twin sharing')->firstOrFail();
        $single = RoomType::where('name', 'Single room')->firstOrFail();
        $this->package->update([
            'room_options' => [$twin->id => ['enabled' => true, 'adjustment' => null], $single->id => ['enabled' => true, 'adjustment' => 600]],
            'default_room_type_id' => $twin->id,
        ]);

        $this->get("/journeys/{$this->package->slug}/book")->assertSee('Single room')->assertDontSee('Triple sharing');

        $this->book(['room_type_id' => RoomType::where('name', 'Triple sharing')->value('id')])->assertSessionHasErrors('room_type_id');
        $this->book(['room_type_id' => $single->id])->assertSessionHasNoErrors();
        $this->assertEquals(2 * 2000 + 2 * 600, (float) Booking::firstOrFail()->total_estimate);
    }

    public function test_unknown_extras_and_countries_are_rejected(): void
    {
        $this->book(['extras' => ['9']])->assertSessionHasErrors('extras');
        $this->book(['country' => 'Atlantis'])->assertSessionHasErrors('country');
        $this->assertSame(0, Booking::count());
    }

    public function test_admin_saves_prices_and_options_for_a_journey(): void
    {
        $this->actingAs(User::first());
        $single = RoomType::where('name', 'Single room')->firstOrFail();
        $twin = RoomType::where('name', 'Twin sharing')->firstOrFail();

        $this->put("/admin/packages/{$this->package->slug}", [
            'title' => $this->package->title, 'duration_days' => 9, 'price' => 2400, 'difficulty' => 'Easy', 'is_published' => '1',
            'child_price' => 1200,
            'tiers' => [['min' => 8, 'price' => 2200]],
            'rooms' => [$twin->id => ['enabled' => '1', 'adjustment' => ''], $single->id => ['enabled' => '1', 'adjustment' => 500]],
            'default_room_type_id' => $twin->id,
            'extras' => [['name' => 'Langar seva', 'description' => '', 'price' => 0, 'per' => 'booking']],
        ])->assertSessionHasNoErrors();

        $this->package->refresh();
        $this->assertSame('1200.00', $this->package->child_price);
        $this->assertSame([['min' => 8, 'price' => 2200]], $this->package->group_tiers);
        $this->assertEquals(500, $this->package->room_options[$single->id]['adjustment']);
        $this->assertSame('Langar seva', $this->package->extras[0]['name']);
    }

    public function test_admin_manages_room_types(): void
    {
        $this->actingAs(User::first());
        $twin = RoomType::where('name', 'Twin sharing')->firstOrFail();

        $this->get('/admin/room-types')->assertOk()->assertSee('Twin sharing');
        $this->put('/admin/room-types', ['types' => [
            ['id' => $twin->id, 'name' => 'Twin sharing', 'description' => 'Two beds', 'default_adjustment' => 0, 'is_active' => '1'],
            ['id' => null, 'name' => 'Suite', 'description' => 'Extra space', 'default_adjustment' => 900, 'is_active' => '1'],
        ]])->assertSessionHasNoErrors();

        $this->assertSame(['Twin sharing', 'Suite'], RoomType::orderBy('sort_order')->pluck('name')->all());
    }
}
