@if ($featurePackage)
    <a href="{{ route('packages.show', $featurePackage) }}" class="absolute -bottom-5 left-4 z-10 flex max-w-[16rem] items-center gap-3 rounded-2xl bg-white p-3 pr-4 shadow-xl ring-1 ring-slate-200 transition hover:ring-brand-300 lg:-left-8">
        <x-media :src="$featurePackage->hero_image_url" class="size-12 shrink-0 rounded-xl"/>
        <span class="min-w-0">
            <span class="block truncate text-sm font-semibold text-slate-900">{{ $featurePackage->category?->name ?? $featurePackage->title }}</span>
            <span class="block text-xs text-slate-500">{{ $featurePackage->duration_days }} days from {{ money($featurePackage->price) }}</span>
        </span>
    </a>
@endif
