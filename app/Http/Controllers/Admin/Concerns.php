<?php

namespace App\Http\Controllers\Admin;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait Concerns
{
    /**
     * An image can be uploaded (`{field}_file`) or given as a URL/path (`{field}`).
     * An upload wins; otherwise the text value is kept as-is.
     */
    protected function imageFrom(Request $request, string $field, ?string $current = null): ?string
    {
        if ($request->hasFile($field.'_file')) {
            return $request->file($field.'_file')->store('uploads', 'public');
        }

        $value = $request->has($field) ? $request->input($field) : $current;

        // Remote images are added to the media library so they can be imported locally.
        if (MediaAsset::isImportable($value)) {
            MediaAsset::register($value);
        }

        return $value;
    }

    protected function imageRules(string $field): array
    {
        return [
            $field => 'nullable|string|max:2048',
            $field.'_file' => 'nullable|image|max:8192',
        ];
    }

    /** Turn a textarea (one item per line) into a clean array. */
    protected function lines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))
            ->map(fn ($line) => trim($line))->filter()->values()->all();
    }

    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(6);
        $slug = $base;
        $i = 2;
        while ($modelClass::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
