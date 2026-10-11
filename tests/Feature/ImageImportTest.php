<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\MediaAsset;
use App\Models\Package;
use App\Models\Page;
use App\Models\User;
use App\Support\ImageImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageImportTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function fakeImages(): void
    {
        Http::fake([
            '*Attabad*' => Http::response('not found', 404),
            '*' => Http::response(str_repeat('x', 2048), 200, ['Content-Type' => 'image/jpeg']),
        ]);
    }

    public function test_seeding_registers_remote_images(): void
    {
        $this->assertGreaterThan(10, MediaAsset::count());
        $this->assertSame(MediaAsset::count(), MediaAsset::where('status', 'pending')->count());
    }

    public function test_command_downloads_and_localizes_images(): void
    {
        Storage::fake('public');
        $this->fakeImages();

        $this->artisan('images:import')->assertSuccessful();

        $lahore = Destination::where('slug', 'lahore')->first();
        $this->assertStringStartsWith('media/', $lahore->image);
        Storage::disk('public')->assertExists($lahore->image);
        $this->assertStringStartsWith('media/', Page::system('home')->sections()->where('type', 'hero')->first()->data['image']);

        // The failed download keeps its remote URL in the content and is reported.
        $hunza = Destination::where('slug', 'hunza-valley')->first();
        $this->assertStringStartsWith('https://', $hunza->image);
        $this->assertSame('failed', MediaAsset::where('source_url', $hunza->image)->value('status'));
        $this->assertSame('HTTP 404', MediaAsset::where('source_url', $hunza->image)->value('error'));

        // Gallery arrays are localized too.
        $this->assertTrue(collect(Package::first()->gallery)->contains(fn ($g) => str_starts_with($g, 'media/')));
    }

    public function test_missing_files_are_downloaded_again(): void
    {
        Storage::fake('public');
        $this->fakeImages();
        $this->artisan('images:import');

        $asset = MediaAsset::where('status', 'imported')->first();
        Storage::disk('public')->delete($asset->path);
        $this->assertCount(1, app(ImageImporter::class)->queue());

        $this->artisan('images:import');
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_non_images_are_rejected(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('<html>', 200, ['Content-Type' => 'text/html'])]);

        $asset = MediaAsset::first();
        $this->assertFalse(app(ImageImporter::class)->download($asset));
        $this->assertStringContainsString('Not an image', $asset->fresh()->error);
    }

    public function test_admin_media_library(): void
    {
        Storage::fake('public');
        $this->fakeImages();
        $this->actingAs(User::first());

        $this->get('/admin/media')->assertOk()->assertSee('Import now');

        $this->post('/admin/media', ['urls' => "https://example.com/a.jpg\nnot-a-url\nhttps://example.com/a.jpg"])->assertSessionHas('status');
        $this->assertSame(1, MediaAsset::where('source_url', 'https://example.com/a.jpg')->count());

        // The page calls the import endpoint repeatedly until nothing remains.
        $skip = [];
        $first = true;
        for ($i = 0; $i < 20; $i++) {
            $data = $this->postJson('/admin/media/import', ['first' => $first, 'skip' => $skip])->assertOk()->json();
            $first = false;
            $skip = array_merge($skip, array_column($data['results'], 'id'));
            if ($data['remaining'] === 0) {
                break;
            }
        }
        $this->assertSame(0, $data['remaining']);
        $this->assertSame(0, MediaAsset::where('status', 'pending')->count());
        $this->assertStringStartsWith('media/', Destination::where('slug', 'lahore')->value('image'));

        $this->get('/admin/media?status=failed')->assertOk()->assertSee('HTTP 404');

        // Saving a remote URL in a form registers it for import.
        $this->put('/admin/destinations/lahore', ['name' => 'Lahore', 'region' => 'Punjab', 'image' => 'https://example.com/new-lahore.jpg']);
        $this->assertTrue(MediaAsset::where('source_url', 'https://example.com/new-lahore.jpg')->exists());
    }
}
