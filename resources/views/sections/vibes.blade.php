@php $dark = $s['background'] === 'dark'; @endphp
@if ($vibes->isNotEmpty())
<x-builder.section :section="$section" :background="$s['background']">
    <div class="container-x">
        <x-builder.heading :s="$s" :dark="$dark"/>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($vibes as $vibe)
                <a href="{{ route('packages.index', ['vibe' => $vibe->slug]) }}" @class(['group flex items-center gap-3 rounded-2xl p-4 ring-1 transition hover:-translate-y-0.5', $dark ? 'bg-white/5 ring-white/10 hover:bg-white/10' : 'bg-white ring-slate-200 hover:shadow-md hover:ring-brand-300']) data-reveal>
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white"><x-icon :name="$vibe->icon" class="size-5.5"/></span>
                    <span class="min-w-0">
                        <span @class(['block truncate text-sm font-semibold', $dark ? 'text-white' : 'text-slate-900'])>{{ $vibe->name }}</span>
                        <span @class(['block text-xs', $dark ? 'text-white/60' : 'text-slate-500'])>{{ $vibe->packages_count }} {{ Str::plural('journey', $vibe->packages_count) }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-builder.section>
@endif
