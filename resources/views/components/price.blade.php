@props(['amount', 'original' => null, 'size' => 'md', 'light' => false])
@php $secondary = currencies()->secondary(); @endphp
<div {{ $attributes }}>
    <p class="flex flex-wrap items-baseline gap-x-2">
        <span @class(['font-bold tracking-tight', 'text-base' => $size === 'sm', 'text-2xl' => $size === 'md', 'text-3xl' => $size === 'lg', $light ? 'text-white' : 'text-slate-900'])>{{ money($amount) }}</span>
        @if ($original && $original > $amount)
            <span @class(['text-sm line-through', $light ? 'text-white/50' : 'text-slate-400'])>{{ money($original) }}</span>
        @endif
        <span @class(['text-sm', $light ? 'text-white/70' : 'text-slate-500'])>per person</span>
    </p>
    @if ($secondary)
        <p @class(['text-xs', $light ? 'text-white/60' : 'text-slate-500'])>{{ currencies()->current()->code }} · about {{ money($amount, $secondary->code) }}</p>
    @endif
</div>
