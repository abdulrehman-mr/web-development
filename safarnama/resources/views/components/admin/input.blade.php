@props(['name', 'label', 'value' => null, 'type' => 'text', 'help' => null])
@php $id = 'f_'.str_replace(['[', ']', '.'], '_', $name); @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" {{ $attributes->except('class')->merge(['class' => 'field']) }}>
    @if ($help)<p class="mt-1 text-xs text-ink/50">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
