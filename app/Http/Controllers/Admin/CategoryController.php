<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use Concerns;

    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('packages')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request)
    {
        $this->save($request, new Category);

        return redirect()->route('admin.categories.index')->with('status', 'Experience created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('status', 'Experience saved.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('status', 'Experience deleted.');
    }

    private function save(Request $request, Category $category): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'tagline' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ] + $this->imageRules('image'));

        $category->fill([
            'name' => $data['name'],
            'slug' => $category->slug ?: $this->uniqueSlug(Category::class, $data['name']),
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'image' => $this->imageFrom($request, 'image', $category->image),
        ])->save();
    }
}
