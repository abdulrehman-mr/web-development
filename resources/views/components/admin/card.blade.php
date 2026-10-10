@props(['title' => null, 'description' => null])
<section {{ $attributes->merge(['class' => 'min-w-0 rounded-2xl bg-white p-5 ring-1 ring-slate-200 sm:p-6']) }}>
    @if ($title)
        <header class="mb-5">
            <h2 class="font-semibold text-slate-900">{{ $title }}</h2>
            @if ($description)<p class="mt-0.5 text-sm text-ink/55">{{ $description }}</p>@endif
        </header>
    @endif
    {{ $slot }}
</section>
