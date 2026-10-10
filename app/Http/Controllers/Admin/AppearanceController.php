<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function edit(): View
    {
        return view('admin.appearance', ['presets' => Theme::PRESETS, 'colors' => Theme::colors()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme_preset' => ['required', Rule::in([...array_keys(Theme::PRESETS), 'custom'])],
            'theme_brand' => ['required_if:theme_preset,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_accent' => ['required_if:theme_preset,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        Setting::put([
            'theme_preset' => $data['theme_preset'],
            'theme_brand' => $data['theme_brand'] ?? setting('theme_brand'),
            'theme_accent' => $data['theme_accent'] ?? setting('theme_accent'),
        ]);

        return back()->with('status', 'Colour scheme saved.');
    }
}
