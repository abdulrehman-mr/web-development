<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\PageBuilder;
use App\Support\SectionTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    public function store(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(SectionTypes::forPage($page->slug)))],
            'after' => 'nullable|integer',
        ]);

        // New sections go after the chosen one, or at the end.
        $sections = $page->sections()->get();
        $position = isset($data['after']) ? $sections->search(fn ($s) => $s->id === (int) $data['after']) : false;
        $position = $position === false ? $sections->count() : $position + 1;

        $section = $page->sections()->create(['type' => $data['type'], 'data' => SectionTypes::defaults($data['type'])]);
        $sections->splice($position, 0, [$section]);
        $this->saveOrder($sections->pluck('id')->all());

        return redirect()->route('admin.pages.sections.edit', [$page, $section])->with('status', $section->label().' added. Fill it in below.');
    }

    public function edit(Page $page, PageSection $section): View
    {
        return view('admin.pages.section', [
            'page' => $page,
            'section' => $section,
            'type' => SectionTypes::get($section->type),
            'values' => $section->values(),
            'options' => $this->dynamicOptions(),
            'links' => $this->siteLinks(),
        ]);
    }

    public function update(Request $request, Page $page, PageSection $section, PageBuilder $builder): RedirectResponse
    {
        $request->validate($builder->fileRules($section->type));

        $section->update([
            'data' => $builder->normalize($section->type, (array) $request->input('data', []), (array) $request->file('files', [])),
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return back()->with('status', $section->label().' saved.');
    }

    public function duplicate(Page $page, PageSection $section): RedirectResponse
    {
        $copy = $section->replicate();
        $copy->save();

        $ids = $page->sections()->pluck('id')->reject(fn ($id) => $id === $copy->id)->values();
        $ids->splice($ids->search($section->id) + 1, 0, [$copy->id]);
        $this->saveOrder($ids->all());

        return back()->with('status', $section->label().' duplicated.');
    }

    public function toggle(Page $page, PageSection $section): RedirectResponse
    {
        $section->update(['is_enabled' => ! $section->is_enabled]);

        return back()->with('status', $section->label().($section->is_enabled ? ' shown.' : ' hidden.'));
    }

    public function destroy(Page $page, PageSection $section): RedirectResponse
    {
        $section->delete();

        return redirect()->route('admin.pages.edit', $page)->with('status', $section->label().' removed.');
    }

    public function reorder(Request $request, Page $page): JsonResponse|RedirectResponse
    {
        $ids = $request->validate(['order' => 'required|array', 'order.*' => 'integer'])['order'];
        $ids = array_values(array_intersect(array_map('intval', $ids), $page->sections()->pluck('id')->all()));
        $this->saveOrder($ids);

        return $request->expectsJson() ? response()->json(['saved' => true]) : back()->with('status', 'Order saved.');
    }

    /** @param  list<int>  $ids */
    private function saveOrder(array $ids): void
    {
        foreach ($ids as $i => $id) {
            PageSection::whereKey($id)->update(['sort_order' => $i]);
        }
    }

    /** @return array<string, array<string, string>> */
    private function dynamicOptions(): array
    {
        $names = config('countries');
        $countries = Destination::query()->whereNotNull('country')->distinct()->pluck('country')
            ->merge(Package::query()->whereNotNull('country')->distinct()->pluck('country'))
            ->unique()->mapWithKeys(fn ($code) => [$code => $names[$code] ?? $code])->sort()->all();

        return [
            'package' => Package::orderBy('title')->pluck('title', 'slug')->all(),
            'category' => Category::orderBy('sort_order')->pluck('name', 'slug')->all(),
            'country' => $countries,
        ];
    }

    /** Suggestions for link fields. @return array<string, string> */
    private function siteLinks(): array
    {
        return Page::orderByDesc('is_system')->orderBy('title')->get()
            ->mapWithKeys(fn (Page $p) => ['/'.ltrim(parse_url($p->url(), PHP_URL_PATH) ?? '', '/') => $p->title])->all();
    }
}
