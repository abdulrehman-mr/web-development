@php
    $dark = $s['background'] === 'dark';
    $link = match ($s['source']) {
        'category' => route('packages.index', ['category' => $s['category']]),
        'country' => route('packages.index', ['country' => $s['country']]),
        default => route('packages.index'),
    };
@endphp
@if ($packages->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark" :href="$link"/>
        <div @class(['grid gap-x-6 gap-y-10 sm:grid-cols-2', 'xl:grid-cols-4' => $s['columns'] === '4', 'lg:grid-cols-3' => $s['columns'] === '3', $dark ? '[&_h3]:text-white' : ''])>
            @foreach ($packages as $package)
                <x-package-card :package="$package"/>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
