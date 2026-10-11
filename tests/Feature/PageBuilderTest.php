<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Setting;
use App\Models\User;
use App\Support\PageBuilder;
use App\Support\SectionTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function homeHero(): PageSection
    {
        return Page::system('home')->sections()->where('type', 'hero')->first();
    }

    private function setHero(array $values): void
    {
        $hero = $this->homeHero();
        $hero->update(['data' => array_replace($hero->values(), $values)]);
    }

    public function test_built_in_pages_keep_their_content_as_sections(): void
    {
        $this->assertSame(['home', 'journeys', 'destinations', 'stays', 'about', 'contact'], Page::orderBy('id')->pluck('slug')->all());

        $this->get('/')->assertOk()->assertSee('Pakistan, the way it deserves to be seen')->assertSee('Footsteps of Guru Nanak');
        $this->get('/destinations')->assertOk()->assertSee('places, each one we know first-hand', false);
        $this->get('/contact')->assertOk()->assertSee('Let’s plan your journey', false);
    }

    /** @return array<string, array{0: string}> */
    public static function heroLayouts(): array
    {
        return array_map(fn ($layout) => [$layout], array_combine(
            array_keys(SectionTypes::get('hero')['fields']['layout']['options']),
            array_keys(SectionTypes::get('hero')['fields']['layout']['options']),
        ));
    }

    #[DataProvider('heroLayouts')]
    public function test_every_hero_layout_renders_with_search_buttons_and_badges(string $layout): void
    {
        $this->setHero([
            'layout' => $layout, 'title' => 'Layout headline', 'primary_label' => 'Plan your journey',
            'search' => true, 'search_style' => 'bar', 'quick_links' => 'categories', 'proof_text' => '2,500 guests',
            'slides' => [['image' => 'https://example.com/a.jpg', 'title' => 'Slide one', 'subtitle' => ''], ['image' => 'https://example.com/b.jpg', 'title' => '', 'subtitle' => '']],
        ]);

        $response = $this->get('/')->assertOk()->assertSee('Plan your journey')->assertSee('2,500 guests')->assertSee('Airport welcome');
        $response->assertSee($layout === 'slider' ? 'Slide one' : 'Layout headline');
        $response->assertSee('name="where"', false);
    }

    public function test_menu_floats_over_a_full_width_photo_hero_only_when_asked(): void
    {
        $this->setHero(['layout' => 'background', 'width' => 'full', 'header_overlay' => true]);
        $this->get('/')->assertSee('site-header fixed inset-x-0 top-0 z-50 bg-white/95 backdrop-blur transition-[background-color,box-shadow] duration-300 is-clear', false);

        $this->setHero(['width' => 'contained']);
        $this->get('/')->assertDontSee('duration-300 is-clear', false);
    }

    /** @return array<string, array{0: string, 1: ?string}> */
    public static function videoLinks(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=3', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?'],
            'youtube short link' => ['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?'],
            'youtube shorts' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?'],
            'vimeo' => ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871?'],
            'not a video' => ['https://example.com/watch?v=dQw4w9WgXcQ', null],
        ];
    }

    #[DataProvider('videoLinks')]
    public function test_youtube_and_vimeo_links_become_muted_background_embeds(string $url, ?string $expected): void
    {
        $embed = app(PageBuilder::class)->videoEmbed($url, true);

        if ($expected === null) {
            $this->assertNull($embed);

            return;
        }
        $this->assertStringStartsWith($expected, $embed['src']);
        $this->assertMatchesRegularExpression('/(mute=1|muted=1)/', $embed['src']);
    }

    public function test_hero_plays_the_chosen_youtube_video(): void
    {
        $this->setHero(['layout' => 'background', 'media_type' => 'youtube', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ']);

        $this->get('/')->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_guests_are_sent_to_login_from_the_page_builder(): void
    {
        $page = Page::system('home');

        $this->get('/admin/pages')->assertRedirect('/admin/login');
        $this->put("/admin/pages/home/sections/{$page->sections()->first()->id}", ['data' => ['title' => 'Hacked']])->assertRedirect('/admin/login');
    }

    public function test_admin_saves_a_section_keeping_only_known_and_safe_values(): void
    {
        $this->actingAs(User::first());
        $hero = $this->homeHero();

        $this->get("/admin/pages/home/sections/{$hero->id}/edit")->assertOk()->assertSee('Photo with destination cards');

        $this->put("/admin/pages/home/sections/{$hero->id}", ['is_enabled' => '1', 'data' => [
            'layout' => 'not-a-layout',
            'title' => 'New <script>alert(1)</script> headline',
            'primary_label' => 'Go',
            'primary_url' => 'javascript:alert(1)',
            'secondary_url' => '/journeys?vibe=romantic',
            'overlay_opacity' => '400',
            'search_fields' => ['where', 'evil'],
            'badges' => [['icon' => 'not-an-icon', 'text' => 'Fast replies'], ['icon' => 'star', 'text' => '']],
            'is_admin' => '1',
        ]])->assertSessionHas('status');

        $data = $hero->fresh()->data;
        $this->assertSame('background', $data['layout']);
        $this->assertSame('', $data['primary_url']);
        $this->assertSame('/journeys?vibe=romantic', $data['secondary_url']);
        $this->assertSame(95, $data['overlay_opacity']);
        $this->assertSame(['where'], $data['search_fields']);
        $this->assertSame([['icon' => 'check', 'text' => 'Fast replies']], $data['badges']);
        $this->assertArrayNotHasKey('is_admin', $data);

        $this->get('/')->assertSee('New &lt;script&gt;alert(1)&lt;/script&gt; headline', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_uploads_hero_and_slide_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::first());
        $hero = $this->homeHero();

        $this->put("/admin/pages/home/sections/{$hero->id}", ['is_enabled' => '1', 'data' => [
            'layout' => 'slider', 'slides' => [['image' => '', 'title' => 'Hunza']],
        ], 'files' => [
            'image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
            'slides' => [['image' => UploadedFile::fake()->image('hunza.jpg', 1600, 900)]],
        ]])->assertSessionHasNoErrors();

        $data = $hero->fresh()->data;
        Storage::disk('public')->assertExists($data['image']);
        Storage::disk('public')->assertExists($data['slides'][0]['image']);
    }

    public function test_upload_that_is_not_an_image_is_rejected(): void
    {
        $this->actingAs(User::first());
        $hero = $this->homeHero();

        $this->put("/admin/pages/home/sections/{$hero->id}", ['data' => ['layout' => 'split'], 'files' => ['image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')]])
            ->assertSessionHasErrors('files.image');
    }

    public function test_admin_adds_a_section_below_another_and_reorders_sections(): void
    {
        $this->actingAs(User::first());
        $page = Page::system('about');
        $first = $page->sections->first();

        $this->post('/admin/pages/about/sections', ['type' => 'gallery', 'after' => $first->id])->assertRedirect();
        $gallery = $page->sections()->where('type', 'gallery')->first();
        $this->assertSame([$first->id, $gallery->id], $page->sections()->take(2)->pluck('id')->all());

        $reversed = $page->sections()->pluck('id')->reverse()->values()->all();
        $this->putJson('/admin/pages/about/order', ['order' => $reversed])->assertOk()->assertJson(['saved' => true]);
        $this->assertSame($reversed, $page->sections()->pluck('id')->all());
    }

    public function test_journey_results_section_can_only_be_added_to_the_journeys_page(): void
    {
        $this->actingAs(User::first());

        $this->post('/admin/pages/about/sections', ['type' => 'journeys_list'])->assertSessionHasErrors('type');
        $this->post('/admin/pages/journeys/sections', ['type' => 'journeys_list'])->assertSessionHasNoErrors();
    }

    public function test_section_of_another_page_returns_404(): void
    {
        $this->actingAs(User::first());
        $contactSection = Page::system('contact')->sections()->first();

        $this->get("/admin/pages/home/sections/{$contactSection->id}/edit")->assertNotFound();
    }

    public function test_hidden_and_removed_sections_disappear_from_the_site(): void
    {
        $this->actingAs(User::first());
        $faq = Page::system('home')->sections()->where('type', 'faq')->first();
        $steps = Page::system('home')->sections()->where('type', 'steps')->first();

        $this->patch("/admin/pages/home/sections/{$faq->id}/toggle");
        $this->delete("/admin/pages/home/sections/{$steps->id}");

        $this->get('/')->assertDontSee('Questions we hear most')->assertDontSee('From first message to farewell dinner');
        $this->assertFalse($faq->fresh()->is_enabled);
    }

    public function test_admin_creates_a_page_that_guests_see_once_published(): void
    {
        $this->actingAs(User::first());

        $this->post('/admin/pages', ['title' => 'Umrah guide', 'is_published' => '0', 'show_in_menu' => '1'])
            ->assertRedirect('/admin/pages/umrah-guide/edit');
        $page = Page::where('slug', 'umrah-guide')->first();
        $this->assertSame(['hero', 'text', 'cta'], $page->sections()->pluck('type')->all());

        $this->get('/umrah-guide')->assertOk();
        auth()->logout();
        $this->get('/umrah-guide')->assertNotFound();
        $this->get('/')->assertDontSee('href="'.url('/umrah-guide').'"', false);

        $page->update(['is_published' => true]);
        $this->get('/umrah-guide')->assertOk()->assertSee('Umrah guide');
        $this->get('/')->assertSee('href="'.url('/umrah-guide').'"', false);
    }

    public function test_page_address_cannot_take_over_a_site_address(): void
    {
        $this->actingAs(User::first());

        $this->post('/admin/pages', ['title' => 'Journeys'])->assertSessionHasErrors(['slug' => 'The selected slug is invalid.']);
        $this->post('/admin/pages', ['title' => 'Copy', 'slug' => 'home'])->assertSessionHasErrors('slug');
        $this->assertSame(6, Page::count());
    }

    public function test_built_in_pages_cannot_be_deleted_or_moved(): void
    {
        $this->actingAs(User::first());

        $this->delete('/admin/pages/about')->assertForbidden();
        $this->put('/admin/pages/about', ['title' => 'Our story', 'slug' => 'story'])->assertSessionHas('status');
        $this->assertSame('Our story', Page::where('slug', 'about')->value('title'));
        $this->get('/about')->assertOk()->assertSee('<title>Our story', false);
    }

    public function test_menu_and_footer_text_come_from_site_content(): void
    {
        Setting::put(['menu_items' => "Umrah|/journeys?vibe=religious\nBlog|https://blog.example.com", 'menu_experiences' => '', 'footer_note' => 'All prices per adult.']);

        $this->get('/')->assertSee('Umrah')->assertSee('https://blog.example.com')->assertSee('All prices per adult.')
            ->assertDontSee('Experiences <svg', false);
    }

    public function test_filtered_journeys_retitle_the_journeys_hero(): void
    {
        $this->get('/journeys')->assertSee('Private, tailor-made journeys');
        $this->get('/journeys?category=sikh-yatra')->assertSee('Sikh Yatra')->assertDontSee('Private, tailor-made journeys');
    }

    public function test_spotlight_falls_back_to_the_first_featured_journey(): void
    {
        $spotlight = Page::system('home')->sections()->where('type', 'spotlight')->first();
        $spotlight->update(['data' => ['package' => 'no-such-journey'] + $spotlight->data]);
        $featured = Package::published()->where('is_featured', true)->orderBy('sort_order')->first();

        $this->get('/')->assertOk()->assertSeeInOrder(['For the sangat', $featured->title]);
    }
}
