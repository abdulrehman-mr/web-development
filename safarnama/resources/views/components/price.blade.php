@props(['amount', 'original' => null, 'size' => 'md', 'light' => false])
@php $rate = (float) setting('cad_rate'); @endphp
<div {{ $attributes }}>
    <div class="flex items-baseline gap-2">
        <span @class(['font-display font-semibold', 'text-2xl' => $size === 'md', 'text-4xl' => $size === 'lg', $light ? 'text-marble-50' : 'text-pine-900'])>{{ money($amount) }}</span>
        @if ($original && $original > $amount)
            <span @class(['text-sm line-through', $light ? 'text-marble-50/50' : 'text-ink/40'])>{{ money($original) }}</span>
        @endif
    </div>
    <p @class(['text-xs', $light ? 'text-marble-50/60' : 'text-ink/50'])>
        USD per person @if ($rate > 0) · ≈ {{ money($amount * $rate, 'CAD') }} @endif
    </p>
</div>
