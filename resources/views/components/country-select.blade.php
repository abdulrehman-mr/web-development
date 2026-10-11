{{-- Searchable country picker. Submits the country name. --}}
@props(['name' => 'country', 'label' => 'Country', 'value' => null, 'id' => 'country'])
@php
    $countries = array_values(config('countries'));
    $popular = ['Canada', 'United States', 'United Kingdom', 'Australia', 'Pakistan', 'United Arab Emirates', 'India', 'Germany'];
@endphp
<div x-data="countrySelect(@js($countries), @js($popular), @js(old($name, $value)))" @click.outside="close()" {{ $attributes->class('relative') }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" :value="selected">
    <div class="relative">
        <x-icon name="globe" class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-slate-400"/>
        <input id="{{ $id }}" type="text" x-model="query" autocomplete="off" role="combobox" aria-autocomplete="list" :aria-expanded="open" aria-controls="{{ $id }}-list"
               placeholder="Start typing your country…" class="field pr-10 pl-10"
               @focus="show()" @click="show()" @input="open = true; active = 0"
               @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter.prevent="results[active] && choose(results[active])" @keydown.escape="close()" @keydown.tab="close()">
        <button type="button" tabindex="-1" @click="open ? close() : (show(), $el.previousElementSibling.focus())" class="absolute top-1/2 right-2 grid size-8 -translate-y-1/2 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Show countries">
            <x-icon name="chevron-down" class="size-4 transition" ::class="open && 'rotate-180'"/>
        </button>
    </div>
    <ul x-cloak x-show="open" x-ref="list" id="{{ $id }}-list" role="listbox"
        class="absolute z-30 mt-1.5 max-h-64 w-full overflow-y-auto rounded-xl bg-white p-1 shadow-xl ring-1 ring-slate-200">
        <template x-for="(country, i) in results" :key="country">
            <li role="option" :aria-selected="country === selected" :data-active="i === active"
                @mousedown.prevent="choose(country)" @mouseenter="active = i"
                :class="i === active ? 'bg-brand-50 text-brand-900' : 'text-slate-700'"
                class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-sm">
                <span x-text="country"></span>
                <x-icon name="check" class="size-4 text-brand-700" x-show="country === selected"/>
            </li>
        </template>
        <li x-show="results.length === 0" class="px-3 py-2 text-sm text-slate-500">No country matches “<span x-text="query"></span>”</li>
    </ul>
    @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
