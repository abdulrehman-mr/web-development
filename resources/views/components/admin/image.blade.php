{{-- Image picker: upload a file or paste a URL, with a live preview. --}}
@props(['name', 'label' => 'Image', 'value' => null, 'help' => null])
<div {{ $attributes->merge(['class' => '']) }} x-data="{ url: @js(old($name, $value)), preview: @js(media_url(old($name, $value))) }">
    <span class="label">{{ $label }}</span>
    <div class="flex flex-col gap-4 sm:flex-row">
        <x-media class="aspect-[4/3] w-full shrink-0 rounded-xl sm:w-40">
            <img x-show="preview" :src="preview" alt="" class="absolute inset-0 size-full object-cover" x-on:error="$el.style.display='none'" x-on:load="$el.style.display=''">
        </x-media>
        <div class="flex-1 space-y-2">
            <input type="text" name="{{ $name }}" x-model="url" @input="preview = url" placeholder="Paste an image URL…" class="field">
            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-ink/60 transition hover:border-brand-600 hover:text-brand-800">
                <x-icon name="plus" class="size-4"/> <span>…or upload a photo</span>
                <input type="file" name="{{ $name }}_file" accept="image/*" class="sr-only" @change="if ($event.target.files[0]) { preview = URL.createObjectURL($event.target.files[0]); }">
            </label>
            @if ($help)<p class="text-xs text-ink/50">{{ $help }}</p>@endif
            @error($name)<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
            @error($name.'_file')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
