@props(['status'])
@php
    $styles = [
        'pending' => 'bg-lake-50 text-lake-700',
        'confirmed' => 'bg-brand-100 text-brand-800',
        'completed' => 'bg-slate-200 text-ink/70',
        'cancelled' => 'bg-rose-500/10 text-rose-600',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize {{ $styles[$status] ?? 'bg-slate-200' }}">{{ $status }}</span>
