<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vibe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VibeController extends Controller
{
    /** Icons that suit vibes; all exist in the icon component. */
    public const ICONS = ['landmark', 'heart', 'moon', 'tree', 'mountain', 'compass', 'wave', 'building', 'utensils', 'music', 'cpu', 'users', 'gem', 'bag', 'leaf', 'sun', 'sparkle', 'plane', 'shield', 'star'];

    public function edit(): View
    {
        return view('admin.vibes', ['vibes' => Vibe::withCount('packages')->orderBy('sort_order')->get(), 'icons' => self::ICONS]);
    }

    /** Saves the whole list at once; removed rows are deleted (journeys simply lose that vibe). */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vibes' => 'nullable|array',
            'vibes.*.id' => 'nullable|integer|exists:vibes,id',
            'vibes.*.name' => 'required|string|max:60',
            'vibes.*.description' => 'nullable|string|max:255',
            'vibes.*.icon' => ['required', Rule::in(self::ICONS)],
        ]);

        DB::transaction(function () use ($data) {
            $kept = [];
            foreach (array_values($data['vibes'] ?? []) as $i => $row) {
                $vibe = Vibe::findOrNew($row['id'] ?? null);
                $slug = Str::slug($row['name']);
                if (Vibe::where('slug', $slug)->where('id', '!=', $vibe->id)->exists()) {
                    $slug .= '-'.($i + 1);
                }
                $vibe->fill(['name' => $row['name'], 'slug' => $slug, 'description' => $row['description'] ?? null, 'icon' => $row['icon'], 'sort_order' => $i])->save();
                $kept[] = $vibe->id;
            }
            Vibe::whereNotIn('id', $kept)->delete();
        });

        return back()->with('status', 'Vibes saved.');
    }
}
