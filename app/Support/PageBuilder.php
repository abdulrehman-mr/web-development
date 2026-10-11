<?php

namespace App\Support;

use App\Http\Controllers\PackageController;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\Hotel;
use App\Models\MediaAsset;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Vibe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Turns page sections into something Blade can render, and cleans what the
 * admin section editor posts so only known fields with sane values are stored.
 */
class PageBuilder
{
    public const SAMPLE_SPOTLIGHT = 'footsteps-of-guru-nanak-sikh-yatra';

    /** Header, footer and label text, editable in Admin → Site content. */
    public const SITE_TEXT = [
        'site_description' => 'Private, fully guided journeys for travellers from Canada, the UK, the US and beyond.',
        'menu_experiences' => 'Experiences',
        'header_button_label' => 'Plan my trip',
        'header_button_url' => '/contact',
        'footer_about' => 'Private, fully guided journeys for travellers from Canada, the UK, the US and beyond.',
        'footer_links_title' => 'Company',
        'footer_contact_title' => 'Talk to us',
        'footer_note' => 'Prices are per person, twin sharing.',
        'whatsapp_label' => 'WhatsApp us',
        'label_phone_primary' => 'Pakistan',
        'label_phone_secondary' => 'Canada',
        'journey_book_button' => 'Choose dates & options',
        'journey_book_note' => 'No payment now. We confirm availability within 24 hours.',
        'journey_call_text' => 'Prefer to talk? Call our Toronto desk on',
        'booking_default_country' => 'Canada',
    ];

    /**
     * Enabled sections of a page, each with its view name and data.
     *
     * @return list<array{section: PageSection, view: string, data: array<string, mixed>}>
     */
    public function blocks(Page $page): array
    {
        return $page->sections()->where('is_enabled', true)->get()
            ->filter(fn (PageSection $s) => SectionTypes::get($s->type) !== null)
            ->values()
            ->map(fn (PageSection $s, int $i) => [
                'section' => $s,
                'view' => 'sections.'.$s->type,
                'data' => ['s' => $s->values(), 'section' => $s, 'isFirst' => $i === 0] + $this->viewData($s),
            ])->all();
    }

    /**
     * Render a page's sections. Extra data (like the journeys list) is shared with every section.
     *
     * @param  array<string, mixed>  $with
     */
    public function view(Page $page, array $with = []): View
    {
        $blocks = $this->blocks($page);

        return view('pages.builder', [
            'page' => $page,
            'blocks' => $blocks,
            'overlay' => $this->overlaysHeader($blocks),
            'placeholders' => $this->placeholders($page),
        ] + $with);
    }

    /**
     * Main menu: the admin's own links (or the standard ones) plus pages marked "Show in menu".
     *
     * @return list<array{label: string, url: string, active: bool}>
     */
    public function menu(): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) setting('menu_items')))->map(fn ($l) => trim($l))->filter();

        $links = $lines->isNotEmpty()
            ? $lines->map(fn ($line) => array_map('trim', explode('|', $line, 2)) + [1 => '/'])->all()
            : [['Journeys', route('packages.index')], ['Destinations', route('destinations.index')], ['Stays', route('stays')], ['About', route('about')], ['Contact', route('contact')]];

        foreach (Page::where('show_in_menu', true)->where('is_published', true)->where('is_system', false)->orderBy('title')->get() as $page) {
            $links[] = [$page->title, $page->url()];
        }

        $current = '/'.trim(request()->path(), '/');

        return collect($links)->map(function (array $link) use ($current) {
            $url = str_starts_with($link[1], '/') ? url($link[1]) : $link[1];
            $path = '/'.trim((string) parse_url($url, PHP_URL_PATH), '/');

            return ['label' => $link[0], 'url' => $url, 'active' => $path !== '/' && str_starts_with($current, $path)];
        })->all();
    }

    /** The menu floats over the page when it opens with a full-width photo hero that asks for it. */
    public function overlaysHeader(array $blocks): bool
    {
        $first = $blocks[0] ?? null;
        if (! $first || $first['section']->type !== 'hero') {
            return false;
        }
        $s = $first['data']['s'];

        return (bool) $s['header_overlay'] && $s['width'] === 'full' && in_array($s['layout'], ['background', 'slider', 'cards'], true);
    }

    /** @return array<string, mixed> */
    public function viewData(PageSection $section): array
    {
        $s = $section->values();

        return match ($section->type) {
            'hero' => $this->heroData($s),
            'experiences' => ['categories' => $this->categories()->take((int) $s['limit'])],
            'vibes' => ['vibes' => Vibe::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get()->filter(fn ($v) => $v->packages_count > 0)->values()],
            'journeys' => ['packages' => $this->journeys($s)],
            'spotlight' => ['package' => $this->package($s['package']) ?? Package::published()->where('is_featured', true)->orderBy('sort_order')->first()],
            'destinations' => ['destinations' => Destination::query()
                ->when($s['featured_only'], fn ($q) => $q->where('is_featured', true))
                ->when($s['country'], fn ($q, $c) => $q->where('country', $c))
                ->orderBy('sort_order')->take((int) $s['limit'])->get()],
            'testimonials' => ['testimonials' => Testimonial::where('is_published', true)->with('package')->orderBy('sort_order')->take((int) $s['limit'])->get()],
            'faq' => ['faqs' => Faq::where('is_published', true)->orderBy('sort_order')->get()],
            'video' => ['embed' => $this->videoEmbed($s['video_url'], false)],
            'contact_form' => ['subjects' => Category::orderBy('sort_order')->pluck('name')],
            'destinations_list' => $this->destinationDirectory(),
            'stays_list' => ['hotels' => Hotel::with('destination')->orderByDesc('stars')->orderBy('name')->get()->groupBy('type')],
            default => [],
        };
    }

    /** @return array<string, mixed> */
    private function heroData(array $s): array
    {
        $data = ['embed' => null, 'slides' => [], 'tiles' => collect(), 'cards' => collect(), 'featurePackage' => null, 'quickLinks' => [], 'searchOptions' => []];

        if (in_array($s['media_type'], ['youtube', 'vimeo'], true)) {
            $data['embed'] = $this->videoEmbed($s['video_url'], true);
        }

        if ($s['layout'] === 'slider') {
            $data['slides'] = collect($s['slides'])->filter(fn ($slide) => filled($slide['image'] ?? null))->values()->all()
                ?: [['image' => $s['image'], 'title' => '', 'subtitle' => '']];
        }

        if ($s['layout'] === 'mosaic') {
            $images = collect($s['gallery'])->pluck('image')->filter()->values();
            $data['tiles'] = $images->isNotEmpty() ? $images->map(fn ($i) => media_url($i)) : Destination::where('is_featured', true)->orderBy('sort_order')->take(7)->get()->map->image_url;
        }

        if ($s['layout'] === 'cards') {
            $data['cards'] = match ($s['cards_source']) {
                'journeys' => Package::published()->where('is_featured', true)->orderBy('sort_order')->take(6)->get()
                    ->map(fn ($p) => ['title' => $p->title, 'meta' => $p->duration_days.' days · from '.money($p->price), 'image' => $p->hero_image_url, 'url' => route('packages.show', $p)]),
                'experiences' => $this->categories()->take(6)
                    ->map(fn ($c) => ['title' => $c->name, 'meta' => $c->tagline, 'image' => $c->image_url, 'url' => route('packages.index', ['category' => $c->slug])]),
                default => Destination::where('is_featured', true)->orderBy('sort_order')->take(6)->get()
                    ->map(fn ($d) => ['title' => $d->name, 'meta' => $d->region ?: $d->country_name, 'image' => $d->image_url, 'url' => route('destinations.show', $d)]),
            };
        }

        if (in_array($s['layout'], ['split', 'mosaic'], true)) {
            $data['featurePackage'] = $this->package($s['feature_package']);
        }

        if ($s['search']) {
            $data['searchOptions'] = $this->searchOptions();
            $data['quickLinks'] = match ($s['quick_links']) {
                'categories' => $this->categories()->take(6)->map(fn ($c) => [$c->name, route('packages.index', ['category' => $c->slug])])->all(),
                'vibes' => Vibe::orderBy('sort_order')->take(6)->get()->map(fn ($v) => [$v->name, route('packages.index', ['vibe' => $v->slug])])->all(),
                'countries' => collect($this->countries())->take(6)->map(fn ($name, $code) => [$name, route('packages.index', ['country' => $code])])->values()->all(),
                default => [],
            };
        }

        return $data;
    }

    /** @return array<string, array<string, string>> */
    private function searchOptions(): array
    {
        $grouped = Destination::orderBy('name')->get(['name', 'slug', 'country'])->groupBy('country_name')->sortKeys();
        $where = $grouped->count() > 1
            ? $grouped->flatMap(fn ($group, $country) => ['country:'.$group->first()->country => 'Anywhere in '.$country] + $group->mapWithKeys(fn ($d) => [$d->slug => "\u{00A0}\u{00A0}\u{00A0}".$d->name])->all())->all()
            : $grouped->flatten()->pluck('name', 'slug')->all();

        return [
            'category' => $this->categories()->pluck('name', 'slug')->all(),
            'where' => $where,
            'duration' => ['short' => 'Up to 6 days', 'medium' => '7 – 9 days', 'long' => '10 days or more'],
            'vibe' => Vibe::orderBy('sort_order')->pluck('name', 'slug')->all(),
        ];
    }

    private function categories(): Collection
    {
        return Category::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get()
            ->filter(fn ($c) => $c->packages_count > 0)->values();
    }

    private function journeys(array $s): Collection
    {
        $query = Package::published()->with('category');

        match ($s['source']) {
            'latest' => $query->latest(),
            'cheapest' => $query->orderBy('price'),
            'category' => $query->whereHas('category', fn ($q) => $q->where('slug', $s['category']))->orderBy('sort_order'),
            'country' => $query->where('country', $s['country'])->orderBy('sort_order'),
            default => $query->where('is_featured', true)->orderBy('sort_order'),
        };

        return $query->take((int) $s['limit'])->get();
    }

    private function package(?string $slug): ?Package
    {
        return $slug ? Package::published()->where('slug', $slug)->first() : null;
    }

    /** @return array<string, mixed> */
    private function destinationDirectory(): array
    {
        $destinations = Destination::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get();
        $multipleCountries = $destinations->pluck('country')->unique()->count() > 1;

        return [
            'regions' => $multipleCountries
                ? $destinations->groupBy('country_name')->sortKeys()
                : $destinations->groupBy(fn ($d) => $d->region ?: $d->country_name),
            'multipleCountries' => $multipleCountries,
        ];
    }

    /** Countries that have at least one published journey, code => name. */
    public function countries(): array
    {
        return PackageController::countriesWithJourneys();
    }

    /** Text a hero on this page can include, like {summary} on the destinations page. */
    public function placeholders(Page $page): array
    {
        if ($page->slug !== 'destinations') {
            return [];
        }
        $destinations = Destination::pluck('country');
        $countries = $destinations->unique()->count();

        return ['{summary}' => $destinations->count().' '.str('place')->plural($destinations->count()).($countries > 1 ? ' in '.$countries.' countries' : '')];
    }

    /**
     * Embed details for a YouTube or Vimeo link. As a background it plays muted,
     * looped and without controls.
     *
     * @return array{provider: string, id: string, src: string}|null
     */
    public function videoEmbed(?string $url, bool $background): ?array
    {
        $url = trim((string) $url);

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            $params = $background
                ? ['autoplay' => 1, 'mute' => 1, 'loop' => 1, 'playlist' => $m[1], 'controls' => 0, 'playsinline' => 1, 'rel' => 0, 'modestbranding' => 1, 'disablekb' => 1]
                : ['rel' => 0, 'modestbranding' => 1];

            return ['provider' => 'youtube', 'id' => $m[1], 'src' => 'https://www.youtube-nocookie.com/embed/'.$m[1].'?'.http_build_query($params)];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d{6,12})~', $url, $m)) {
            $params = $background ? ['background' => 1, 'autoplay' => 1, 'loop' => 1, 'muted' => 1, 'dnt' => 1] : ['dnt' => 1];

            return ['provider' => 'vimeo', 'id' => $m[1], 'src' => 'https://player.vimeo.com/video/'.$m[1].'?'.http_build_query($params)];
        }

        return null;
    }

    /**
     * Clean posted values against the section type's fields.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $files  uploaded files, keyed like $input
     * @return array<string, mixed>
     */
    public function normalize(string $type, array $input, array $files = []): array
    {
        return $this->clean(SectionTypes::get($type)['fields'] ?? [], $input, $files);
    }

    /** Validation rules for uploads in a section form. */
    public function fileRules(string $type): array
    {
        $rules = [];
        foreach (SectionTypes::get($type)['fields'] ?? [] as $key => $field) {
            match ($field['type']) {
                'image' => $rules["files.$key"] = 'nullable|image|max:8192',
                'video' => $rules["files.$key"] = 'nullable|file|mimetypes:video/mp4,video/webm|max:51200',
                'repeater' => collect($field['fields'])->filter(fn ($f) => $f['type'] === 'image')
                    ->each(function ($f, $sub) use (&$rules, $key) {
                        $rules["files.$key.*.$sub"] = 'nullable|image|max:8192';
                    }),
                default => null,
            };
        }

        return $rules;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function clean(array $fields, array $input, array $files): array
    {
        $out = [];
        foreach ($fields as $key => $field) {
            $value = $input[$key] ?? null;
            $out[$key] = match ($field['type']) {
                'text' => $this->text($key, $value, 500),
                'textarea' => $this->text($key, $value, 10000),
                'image', 'video' => $this->upload($files[$key] ?? null, $field['type']) ?? $this->media($value),
                'toggle' => filter_var($value, FILTER_VALIDATE_BOOL),
                'number', 'range' => $this->number($value, $field),
                'select', 'choice', 'layout' => array_key_exists((string) $value, $field['options']) ? (string) $value : $field['default'],
                'checkboxes' => array_values(array_intersect(array_keys($field['options']), (array) $value)),
                'icon' => in_array($value, SectionTypes::ICONS, true) ? $value : $field['default'],
                'package', 'category' => is_string($value) && preg_match('/^[a-z0-9-]{0,191}$/', $value) ? $value : '',
                'country' => is_string($value) && isset(config('countries')[$value]) ? $value : '',
                'repeater' => $this->repeater($field, (array) $value, (array) ($files[$key] ?? [])),
                default => null,
            };
        }

        return $out;
    }

    private function repeater(array $field, array $items, array $files): array
    {
        $rows = [];
        foreach ($items as $i => $item) {
            if (! is_array($item)) {
                continue;
            }
            $row = $this->clean($field['fields'], $item, (array) ($files[$i] ?? []));
            $meaningful = collect($row)->except('icon')->filter(fn ($v) => filled($v));
            if ($meaningful->isNotEmpty()) {
                $rows[] = $row;
            }
        }

        return array_slice($rows, 0, $field['max_items'] ?? 50);
    }

    private function text(string $key, mixed $value, int $max): string
    {
        $value = mb_substr(trim(is_scalar($value) ? (string) $value : ''), 0, $max);

        // Links may only point to web pages, paths, anchors, email or phone.
        if (str_ends_with($key, '_url') && $value !== '' && ! preg_match('~^(https?://|/|#|mailto:|tel:)~i', $value)) {
            return '';
        }

        return $value;
    }

    private function number(mixed $value, array $field): int
    {
        $number = is_numeric($value) ? (int) $value : (int) $field['default'];

        return max($field['min'] ?? 0, min($field['max'] ?? PHP_INT_MAX, $number));
    }

    private function media(mixed $value): ?string
    {
        $value = is_string($value) ? mb_substr(trim($value), 0, 2048) : '';
        if ($value === '') {
            return null;
        }
        if (MediaAsset::isImportable($value)) {
            MediaAsset::register($value);
        }

        return $value;
    }

    private function upload(mixed $file, string $type): ?string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        return $file->store($type === 'video' ? 'uploads/video' : 'uploads', 'public');
    }

    /** Every image used by page sections, as [section, path in data]. */
    public function imageFields(): iterable
    {
        foreach (PageSection::all() as $section) {
            foreach (SectionTypes::get($section->type)['fields'] ?? [] as $key => $field) {
                if ($field['type'] === 'image') {
                    yield [$section, [$key]];
                }
                if ($field['type'] === 'repeater') {
                    foreach (array_keys($section->data[$key] ?? []) as $i) {
                        foreach ($field['fields'] as $sub => $subField) {
                            if ($subField['type'] === 'image') {
                                yield [$section, [$key, $i, $sub]];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Create the built-in pages with sections taken from the existing site content.
     * Pages that already exist are left alone.
     */
    public function installDefaults(): void
    {
        // Site-wide text that used to be fixed in the templates; existing values are kept.
        $missing = array_diff_key(self::SITE_TEXT, Setting::query()->whereIn('key', array_keys(self::SITE_TEXT))->pluck('value', 'key')->all());
        if ($missing) {
            Setting::put($missing);
        }

        foreach ($this->defaultPages() as $slug => [$title, $sections]) {
            if (Page::where('slug', $slug)->exists()) {
                continue;
            }
            $page = Page::create(['slug' => $slug, 'title' => $title, 'is_system' => true]);
            foreach ($sections as $i => [$type, $data]) {
                $page->sections()->create([
                    'type' => $type,
                    'data' => array_replace(SectionTypes::defaults($type), $data),
                    'sort_order' => $i,
                ]);
            }
        }
    }

    /** @return array<string, array{0: string, 1: list<array{0: string, 1: array<string, mixed>}>}> */
    private function defaultPages(): array
    {
        $stats = collect(['stat_1', 'stat_2', 'stat_3'])
            ->map(fn ($key) => array_pad(explode('|', (string) setting($key, ['stat_1' => '1,200+|guests hosted since 2014', 'stat_2' => '38|cities and valleys covered', 'stat_3' => '4.9/5|average guest rating'][$key])), 2, ''))
            ->filter(fn ($pair) => $pair[0] !== '')
            ->map(fn ($pair) => ['value' => $pair[0], 'label' => $pair[1]])->values()->all();
        $rating = $stats ? ($stats[2]['value'] ?? $stats[0]['value']).' average from '.$stats[0]['value'].' guests' : '';

        $heroImage = setting('hero_image', 'https://commons.wikimedia.org/wiki/Special:FilePath/BADSHAHI_MOSQUE_LAHORE.jpg?width=2400');
        $storyImage = setting('story_image', 'https://commons.wikimedia.org/wiki/Special:FilePath/Nanga_Parbat_%28Fairy_Meadows%29.jpg?width=1600');
        $aboutImage = setting('about_image', 'https://commons.wikimedia.org/wiki/Special:FilePath/K2_2006b.jpg?width=1600');
        $cta = ['cta', ['title' => 'Ready to plan your journey to Pakistan?', 'image' => $aboutImage]];

        return [
            'home' => ['Home', [
                ['hero', [
                    'layout' => 'split', 'image_side' => 'right', 'image_mask' => 'arch', 'mobile_background' => true, 'background' => 'white',
                    'eyebrow' => setting('hero_eyebrow', 'Private journeys through Pakistan, from Canada and beyond'),
                    'title' => setting('hero_title', 'Pakistan, the way it deserves to be seen'),
                    'subtitle' => setting('hero_subtitle', 'Mughal Lahore, the Karakoram, the gurdwaras of Guru Nanak and the dunes of Cholistan. We meet you at the airport, handle every permit and stay, and travel with you the whole way.'),
                    'image' => $heroImage,
                    'feature_package' => self::SAMPLE_SPOTLIGHT,
                    'search' => true, 'search_style' => 'panel',
                    'badges' => [
                        ['icon' => 'plane', 'text' => 'Airport welcome'],
                        ['icon' => 'document', 'text' => 'E-visa letter'],
                        ['icon' => 'shield', 'text' => 'Licensed guides'],
                        ['icon' => 'phone', 'text' => 'Toronto & Lahore desks'],
                    ],
                ]],
                ['experiences', ['title' => setting('home_experiences_title', 'Six ways to discover Pakistan')]],
                ['vibes', []],
                ['journeys', []],
                ['spotlight', ['package' => self::SAMPLE_SPOTLIGHT, 'eyebrow' => 'For the sangat', 'button_label' => 'See the full yatra']],
                ['destinations', ['title' => setting('home_destinations_title', 'From the Arabian Sea to the Karakoram')]],
                ['story', [
                    'image' => $storyImage,
                    'title' => setting('story_title', 'Hosted by people who call this country home'),
                    'body' => setting('story_body', 'For over a decade our Lahore-based team has guided visitors from Canada, the UK and the Gulf through Pakistan. Every journey is private, every guide is licensed, and every hotel is one we have stayed in ourselves.'),
                    'stats' => $stats,
                ]],
                ['steps', ['title' => 'From first message to farewell dinner', 'items' => [
                    ['title' => 'Tell us your plans', 'text' => 'Pick a journey or describe your dream trip. A travel designer replies within one working day.'],
                    ['title' => 'Shape your itinerary', 'text' => 'We tailor dates, hotels and pace, including visits to family villages or gurdwaras.'],
                    ['title' => 'Secure with a deposit', 'text' => 'A 25% deposit holds your dates. We send your e-visa invitation letter straight away.'],
                    ['title' => 'Land and relax', 'text' => 'We meet you at arrivals and stay with you until your flight home.'],
                ]]],
                ['testimonials', ['rating_text' => $rating]],
                ['faq', []],
                $cta,
            ]],
            'journeys' => ['Journeys', [
                ['hero', ['layout' => 'banner', 'eyebrow' => 'All journeys', 'title' => 'Private, tailor-made journeys', 'subtitle' => 'Every itinerary below is private to your group and can be tailored: longer, shorter, slower, or with family visits along the way.', 'image' => $heroImage, 'content_y' => 'bottom', 'width' => 'contained']],
                ['journeys_list', []],
                $cta,
            ]],
            'destinations' => ['Destinations', [
                ['hero', ['layout' => 'banner', 'eyebrow' => 'Destinations', 'title' => 'Where we travel', 'subtitle' => '{summary}, each one we know first-hand.', 'image' => $aboutImage, 'content_y' => 'bottom', 'width' => 'contained']],
                ['destinations_list', []],
                $cta,
            ]],
            'stays' => ['Where you’ll stay', [
                ['hero', ['layout' => 'banner', 'title' => 'Where you’ll stay', 'subtitle' => 'Restored havelis, lakeside lodges, private desert camps and the best five-star hotels in each city. We have slept in every one of them.', 'image' => $storyImage, 'content_y' => 'bottom', 'width' => 'contained']],
                ['stays_list', []],
                $cta,
            ]],
            'about' => ['About us', [
                ['hero', ['layout' => 'banner', 'eyebrow' => 'About us', 'title' => setting('about_title', 'A travel company built for visitors from abroad'), 'image' => $aboutImage, 'content_y' => 'bottom', 'width' => 'contained']],
                ['text', ['body' => setting('about_body', ''), 'stats' => $stats]],
                ['features', ['items' => [
                    ['icon' => 'plane', 'title' => 'Airport welcome', 'text' => 'Met at arrivals with garlands and driven to your hotel — no queues for taxis.'],
                    ['icon' => 'document', 'title' => 'Visa support', 'text' => 'An invitation letter and step-by-step help with your e-visa.'],
                    ['icon' => 'shield', 'title' => 'Licensed guides', 'text' => 'Government-licensed guides who speak English, Urdu and Punjabi.'],
                    ['icon' => 'users', 'title' => 'Always private', 'text' => 'Your own vehicle, your own guide, your own pace.'],
                    ['icon' => 'bed', 'title' => 'Tested stays', 'text' => 'Every hotel, haveli and camp has been personally inspected.'],
                    ['icon' => 'phone', 'title' => '24/7 support', 'text' => 'One WhatsApp line to our Lahore and Toronto teams, day or night.'],
                ]]],
                ['testimonials', ['title' => 'In our guests’ words', 'background' => 'white']],
                $cta,
            ]],
            'contact' => ['Contact', [
                ['contact_form', []],
                ['faq', ['title' => 'Before you ask', 'intro' => '', 'button_label' => '', 'layout' => 'center', 'background' => 'light']],
            ]],
        ];
    }
}
