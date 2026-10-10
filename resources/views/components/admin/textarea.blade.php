@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null])
@php $id = 'f_'.str_replace(['[', ']', '.'], '_', $name); @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class')->merge(['class' => 'field']) }}>{{ old($name, $value) }}</textarea>
    @if ($help)<p class="mt-1 text-xs text-ink/50">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
