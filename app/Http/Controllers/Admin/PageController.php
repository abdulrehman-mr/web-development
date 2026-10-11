<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\SectionTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    /** First parts of addresses the site already uses, so a page can never hide them. */
    public const RESERVED_SLUGS = ['admin', 'api', 'journeys', 'destinations', 'stays', 'about', 'contact', 'booking', 'payments', 'webhooks', 'currency', 'storage', 'build', 'up', 'login', 'logout', 'home', 'media', 'uploads'];

    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::withCount('sections')->orderByDesc('is_system')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', ['page' => new Page(['is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['title']);
        $request->merge(['slug' => $data['slug']])->validate(['slug' => $this->slugRules()]);

        $page = Page::create($data);

        if ($request->input('start') !== 'empty') {
            foreach ([
                ['hero', ['layout' => 'banner', 'width' => 'contained', 'title' => $page->title, 'content_y' => 'bottom']],
                ['text', ['title' => 'A heading for your page', 'body' => 'Write your page here. Leave a blank line between paragraphs.']],
                ['cta', []],
            ] as $i => [$type, $values]) {
                $page->sections()->create(['type' => $type, 'data' => array_replace(SectionTypes::defaults($type), $values), 'sort_order' => $i]);
            }
        }

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page created. Add, remove and arrange its sections below.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page->load('sections'),
            'types' => SectionTypes::forPage($page->slug),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validated($request);

        if ($page->is_system) {
            unset($data['slug'], $data['show_in_menu']);
            $data['is_published'] = true;
        } else {
            $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['title']);
            $request->merge(['slug' => $data['slug']])->validate(['slug' => $this->slugRules($page)]);
        }

        $page->update($data);

        return back()->with('status', 'Page settings saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        abort_if($page->is_system, 403, 'Built-in pages cannot be deleted. Hide their sections instead.');

        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:160',
            'slug' => 'nullable|string|max:120',
            'meta_description' => 'nullable|string|max:300',
        ]) + [
            'is_published' => $request->boolean('is_published'),
            'show_in_menu' => $request->boolean('show_in_menu'),
        ];
    }

    /** @return list<mixed> */
    private function slugRules(?Page $ignore = null): array
    {
        return ['required', 'regex:/^[a-z0-9-]+$/', Rule::notIn(self::RESERVED_SLUGS), Rule::unique('pages', 'slug')->ignore($ignore)];
    }
}
