<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use Concerns;

    public function edit()
    {
        return view('admin.settings', ['schema' => Setting::SCHEMA]);
    }

    public function update(Request $request)
    {
        $values = [];
        $rules = [];
        foreach (Setting::SCHEMA as $fields) {
            foreach ($fields as $key => [, $type]) {
                $rules += $type === 'image' ? $this->imageRules($key) : [$key => 'nullable|string|max:10000'];
            }
        }
        $request->validate($rules);

        foreach (Setting::SCHEMA as $fields) {
            foreach ($fields as $key => [, $type]) {
                $values[$key] = $type === 'image' ? $this->imageFrom($request, $key, setting($key)) : $request->input($key);
            }
        }

        Setting::put($values);

        return back()->with('status', 'Site content saved.');
    }
}
