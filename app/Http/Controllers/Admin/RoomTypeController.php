<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function edit(): View
    {
        return view('admin.room-types', ['types' => RoomType::orderBy('sort_order')->orderBy('id')->get()]);
    }

    /** Saves the whole list at once; rows removed in the form are deleted. */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'types' => 'nullable|array',
            'types.*.id' => 'nullable|integer|exists:room_types,id',
            'types.*.name' => 'required|string|max:80',
            'types.*.description' => 'nullable|string|max:255',
            'types.*.default_adjustment' => 'required|numeric|min:-100000|max:100000',
            'types.*.is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data) {
            $kept = [];
            foreach (array_values($data['types'] ?? []) as $i => $row) {
                $type = RoomType::findOrNew($row['id'] ?? null);
                $type->fill([
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'default_adjustment' => $row['default_adjustment'],
                    'is_active' => ! empty($row['is_active']),
                    'sort_order' => $i,
                ])->save();
                $kept[] = $type->id;
            }
            RoomType::whereNotIn('id', $kept)->delete();
        });

        return back()->with('status', 'Room types saved.');
    }
}
