{{-- Wrapper for a page-builder section: background, spacing and an anchor. --}}
@props(['section', 'background' => 'white'])
@php
    $classes = ['white' => 'bg-white', 'light' => 'bg-slate-50', 'tint' => 'bg-brand-50', 'dark' => 'bg-brand-950 text-white'];
@endphp
<section id="section-{{ $section->id }}" data-bg="{{ $background }}" {{ $attributes->class(['builder-section py-16 sm:py-24', $classes[$background] ?? 'bg-white']) }}>
    {{ $slot }}
</section>
