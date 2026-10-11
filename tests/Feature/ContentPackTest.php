<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\MediaAsset;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vibe;
use App\Support\BookingPricing;
use App\Support\ContentPacks;
use App\Support\ImageImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentPackTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function packs(): ContentPacks
    {
        return app(ContentPacks::class);
    }

    public function test_every_pack_imports_cleanly_and_only_once(): void
    {
        $this->assertCount(11, $this->packs()->all());

        foreach ($this->packs()->all()->keys() as $key) {
            $created = $this->packs()->import($key);
            $this->assertGreaterThan(0, $created['packages'], "{$key} created no journeys");
        }
        $this->assertSame(0, array_sum(array_slice($this->packs()->import('france'), 0, 3)), 'Re-importing creates nothing.');

        $paris = Package::where('slug', 'paris-in-love')->firstOrFail();
        $this->assertSame('FR', $paris->country);
        $this->assertSame(['Romantic', 'City breaks', 'Food & culture'], $paris->vibes->pluck('name')->all());
        $this->assertSame(5, $paris->days()->count());
        $this->assertSame('wiki:Eiffel Tower', $paris->hero_image);
        $this->assertTrue(MediaAsset::where('source_url', 'wiki:Eiffel Tower')->exists());
        $this->assertSame('CA', Destination::where('slug', 'niagara-falls')->value('country'));
    }

    public function test_prices_are_converted_from_usd_to_the_base_currency(): void
    {
        currencies()->rebase('CAD');
        Setting::put(['base_currency' => 'CAD']);

        $this->packs()->import('saudi-arabia');

        $this->assertEquals(round(1670 * 1.38), Package::where('slug', 'umrah-with-ziyarat-in-makkah-madinah')->value('price'));
    }

    public function test_umrah_guests_choose_sharing_class_and_hotels(): void
    {
        $this->packs()->import('saudi-arabia');
        $umrah = Package::where('slug', 'umrah-with-ziyarat-in-makkah-madinah')->firstOrFail();
        $groups = app(BookingPricing::class)->groups($umrah);

        $this->assertSame(['Package class', 'Hotel in Makkah', 'Hotel in Madinah'], $groups->pluck('name')->all());
        $this->assertSame('Holiday Inn Makkah Al Aziziah', $groups[1]['options'][0]['label']);
        $this->assertSame('Jabal Al Kaaba Street, Makkah', collect($groups[1]['options'])->firstWhere('label', 'Anjum Hotel Makkah')['hotel']['address']);

        $page = $this->get("/journeys/{$umrah->slug}/book")->assertOk();
        $page->assertSee('Quad sharing')->assertSee('Raffles Makkah Palace')->assertSee('VIP');

        $fairmont = collect($groups[1]['options'])->firstWhere('label', 'Fairmont Makkah Clock Royal Tower')['key'];
        $this->post("/journeys/{$umrah->slug}/book", [
            'full_name' => 'Aisha Khan', 'email' => 'aisha@example.ca', 'adults' => 2, 'children' => 0,
            'start_date' => now()->addMonths(3)->toDateString(),
            'room_type_id' => RoomType::where('name', 'Private room')->value('id'),
            'options' => ['0' => '2', '1' => $fairmont],
        ])->assertSessionHasNoErrors();

        $booking = Booking::firstOrFail();
        // 2 × 1670 + 2 × 890 private room + 2 × 650 VIP + 2 × 560 Fairmont (Madinah default included)
        $this->assertEquals(2 * 1670 + 2 * 890 + 2 * 650 + 2 * 560, (float) $booking->total_estimate);
        $this->assertSame(['Package class' => 'VIP', 'Hotel in Makkah' => 'Fairmont Makkah Clock Royal Tower', 'Hotel in Madinah' => 'Anwar Al Madinah Mövenpick'],
            collect($booking->options)->pluck('label', 'group')->all());

        $this->post("/journeys/{$umrah->slug}/book", [
            'full_name' => 'X', 'email' => 'x@example.ca', 'adults' => 1, 'start_date' => now()->addMonth()->toDateString(), 'options' => ['1' => '99'],
        ])->assertSessionHasErrors('options');
    }

    public function test_pack_room_types_do_not_appear_on_other_journeys(): void
    {
        $this->packs()->import('saudi-arabia');

        $pakistan = Package::where('country', 'PK')->whereNull('room_options')->firstOrFail();
        $rooms = app(BookingPricing::class)->rooms($pakistan)->pluck('name');

        $this->assertNotContains('Quad sharing', $rooms);
        $this->assertContains('Twin sharing', $rooms);
    }

    public function test_wikipedia_images_are_resolved_and_downloaded(): void
    {
        Storage::fake('public');
        Http::fake([
            'en.wikipedia.org/*' => fn ($request) => Http::response(['query' => ['pages' => [
                $request['titles'] === 'Eiffel Tower'
                    ? ['title' => 'Eiffel Tower', 'thumbnail' => ['source' => 'https://upload.wikimedia.org/eiffel.jpg']]
                    : ['title' => $request['titles']],
            ]]]),
            'upload.wikimedia.org/*' => Http::response('jpegdata', 200, ['Content-Type' => 'image/jpeg']),
        ]);
        $asset = MediaAsset::register('wiki:Eiffel Tower');

        $this->assertTrue(app(ImageImporter::class)->download($asset));
        Storage::disk('public')->assertExists($asset->fresh()->path);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'titles=Eiffel%20Tower'));

        $missing = MediaAsset::register('wiki:Nowhere');
        $this->assertFalse(app(ImageImporter::class)->download($missing));
        $this->assertStringContainsString('no main image', $missing->fresh()->error);
    }

    public function test_removing_a_pack_keeps_booked_journeys(): void
    {
        $this->packs()->import('turkey');
        $booked = Package::where('slug', 'turkey-grand-tour')->firstOrFail();
        $booked->bookings()->create(['full_name' => 'A', 'email' => 'a@example.com', 'adults' => 1, 'start_date' => now()->addMonth()]);

        $result = $this->packs()->remove('turkey');

        $this->assertSame(1, $result['kept']);
        $this->assertFalse($booked->fresh()->is_published);
        $this->assertNull(Package::where('slug', 'istanbul-cappadocia-balloons')->first());
        $this->assertNotNull(Hotel::where('name', 'Çırağan Palace Kempinski')->first(), 'Hotels used by the kept journey stay.');
    }

    public function test_admin_imports_and_removes_packs(): void
    {
        $this->actingAs(User::first());
        $this->get('/admin/content-packs')->assertOk()->assertSee('Saudi Arabia')->assertSee('Umrah with Ziyarat in Makkah &amp; Madinah', false);

        $this->post('/admin/content-packs/thailand', ['publish' => '1'])->assertSessionHas('status');
        $this->assertTrue(Package::where('pack', 'thailand')->where('is_published', true)->exists());

        $this->post('/admin/content-packs/maldives')->assertSessionHas('status');
        $this->assertFalse(Package::where('pack', 'maldives')->where('is_published', true)->exists(), 'Unticked publish keeps journeys as drafts.');

        $this->delete('/admin/content-packs/thailand')->assertSessionHas('status');
        $this->assertSame(0, Package::where('pack', 'thailand')->count());
        $this->post('/admin/content-packs/atlantis')->assertNotFound();
    }

    public function test_visitors_filter_by_country_and_vibe(): void
    {
        $this->packs()->import('france');
        $this->packs()->import('south-korea');

        $this->get('/journeys?country=FR')->assertSee('Paris in Love')->assertDontSee('Seoul Tech')->assertDontSee('Footsteps of Guru Nanak');
        $this->get('/journeys?where=country:KR')->assertSee('Seoul Tech &amp; K-Culture', false)->assertDontSee('Paris in Love');
        $this->get('/journeys?vibe=tech-savvy')->assertSee('Seoul Tech &amp; K-Culture', false)->assertDontSee('Paris in Love');
        $this->get('/journeys')->assertSee('All countries')->assertSee('Romantic');

        $this->get('/')->assertSee('Find your travel vibe')->assertSee('Anywhere in France');
        $this->get('/destinations')->assertSee('France')->assertSee('South Korea')->assertSee('Pakistan');
        $this->getJson('/api/v1/packages?country=FR')->assertJsonPath('total', 2)->assertJsonPath('data.0.country', 'FR');
    }

    public function test_admin_manages_vibes_and_tags_journeys(): void
    {
        $this->actingAs(User::first());
        $romantic = Vibe::where('slug', 'romantic')->firstOrFail();

        $this->put('/admin/vibes', ['vibes' => [
            ['id' => $romantic->id, 'name' => 'Romantic', 'description' => 'For two', 'icon' => 'heart'],
            ['id' => null, 'name' => 'Desert safari', 'description' => null, 'icon' => 'sun'],
        ]])->assertSessionHasNoErrors();
        $this->assertSame(['Romantic', 'Desert safari'], Vibe::orderBy('sort_order')->pluck('name')->all());

        $package = Package::firstOrFail();
        $this->put("/admin/packages/{$package->slug}", [
            'title' => $package->title, 'duration_days' => 5, 'price' => 1000, 'difficulty' => 'Easy', 'is_published' => '1',
            'country' => 'PK', 'vibe_ids' => [$romantic->id],
            'groups' => [['name' => 'Package class', 'options' => [['label' => 'Economy', 'price' => 0, 'per' => 'person'], ['label' => 'VIP', 'price' => 300, 'per' => 'person']]]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Romantic'], $package->fresh()->vibes->pluck('name')->all());
        $this->assertSame('VIP', $package->fresh()->option_groups[0]['options'][1]['label']);
    }
}
