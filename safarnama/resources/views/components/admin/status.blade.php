@props(['status'])
@php
    $styles = [
        'pending' => 'bg-brass-100 text-brass-700',
        'confirmed' => 'bg-pine-100 text-pine-800',
        'completed' => 'bg-marble-200 text-ink/70',
        'cancelled' => 'bg-sandstone-500/10 text-sandstone-600',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $styles[$status] ?? 'bg-marble-200' }}">{{ $status }}</span>
