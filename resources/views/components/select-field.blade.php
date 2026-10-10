{{-- A dropdown that looks like one: label, leading icon, visible box and chevron. --}}
@props(['name', 'label', 'icon', 'options' => [], 'placeholder' => null, 'selected' => null])
@php $id = $attributes->get('id', 'sf-'.$name); @endphp
<div {{ $attributes->except('id')->class('min-w-0') }}>
    <label for="{{ $id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">{{ $label }}</label>
    <div class="relative">
        <x-icon :name="$icon" class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-brand-600"/>
        <select id="{{ $id }}" name="{{ $name }}" class="field cursor-pointer appearance-none truncate bg-slate-50 py-3 pr-10 pl-10 font-medium text-slate-900 hover:border-slate-400">
            @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
        <x-icon name="chevron-down" class="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-slate-500"/>
    </div>
</div>
