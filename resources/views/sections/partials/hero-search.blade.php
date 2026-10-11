{{-- Journey search in three styles: a card of dropdowns, a one-line bar, or a keyword box. --}}
@php
    $labels = ['category' => ['Experience', 'sparkle', 'Any experience'], 'where' => ['Where', 'map-pin', 'Anywhere'], 'duration' => ['Trip length', 'clock', 'Any length'], 'vibe' => ['Vibe', 'heart', 'Any vibe']];
    $fields = array_values(array_filter($s['search_fields'], fn ($f) => ! empty($searchOptions[$f])));
    $gridCols = [1 => 'sm:grid-cols-1', 2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-3', 4 => 'sm:grid-cols-2 lg:grid-cols-4'][max(1, count($fields))];
    $phonePhoto ??= false;
@endphp
<div @class(['relative', $phonePhoto ? '-mt-16 lg:mt-9' : 'mt-8'])>
    @if ($quickLinks)
        <nav class="mb-3 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] {{ ['left' => '', 'center' => 'sm:justify-center', 'right' => 'sm:justify-end'][$align] }}" aria-label="Quick filters">
            @foreach ($quickLinks as [$label, $url])
                <a href="{{ $url }}" class="shrink-0 rounded-full px-3.5 py-2 text-sm font-medium ring-1 transition {{ $phonePhoto ? 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400' : $tone['chip'] }}">{{ $label }}</a>
            @endforeach
        </nav>
    @endif

    @if ($s['search_style'] === 'minimal')
        <form action="{{ route('packages.index') }}" method="get" class="flex gap-2 rounded-2xl bg-white p-2 text-left shadow-[0_20px_50px_-20px_rgb(15_23_42/0.45)] ring-1 ring-slate-200">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">Search journeys</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-4.5 -translate-y-1/2 text-brand-600"/>
                <input type="search" name="q" placeholder="Where would you like to go?" class="h-full w-full rounded-xl border-0 bg-transparent py-3 pr-3 pl-11 text-sm font-medium text-slate-900 placeholder:text-slate-500 focus:ring-0 focus:outline-none">
            </label>
            <button class="btn btn-primary shrink-0 px-4 sm:px-6"><x-icon name="search" class="size-4.5 sm:hidden"/><span class="hidden sm:inline">{{ $s['search_button'] }}</span></button>
        </form>

    @elseif ($s['search_style'] === 'bar')
        <form action="{{ route('packages.index') }}" method="get" class="rounded-2xl bg-white p-3 text-left shadow-[0_20px_50px_-20px_rgb(15_23_42/0.45)] ring-1 ring-slate-200 sm:rounded-3xl lg:flex lg:items-end lg:gap-2 lg:p-2.5">
            <div class="grid min-w-0 flex-1 gap-3 {{ $gridCols }} lg:gap-2">
                @foreach ($fields as $field)
                    <x-select-field :id="'hs-'.$section->id.'-'.$field" :name="$field" :label="$labels[$field][0]" :icon="$labels[$field][1]" :placeholder="$labels[$field][2]" :options="$searchOptions[$field]"/>
                @endforeach
            </div>
            <button class="btn btn-primary mt-3 w-full py-3.5 lg:mt-0 lg:w-auto lg:rounded-xl lg:px-5" aria-label="{{ $s['search_button'] }}">
                <x-icon name="search" class="size-4.5"/><span class="lg:sr-only">{{ $s['search_button'] }}</span>
            </button>
        </form>

    @else
        <form action="{{ route('packages.index') }}" method="get" class="rounded-2xl bg-white p-4 text-left shadow-[0_20px_50px_-20px_rgb(15_23_42/0.35)] ring-1 ring-slate-200 sm:p-5">
            @if ($s['search_heading'])
                <p class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-900"><x-icon name="search" class="size-4 text-brand-600"/>{{ $s['search_heading'] }}</p>
            @endif
            <div class="grid gap-3 {{ $gridCols }}">
                @foreach ($fields as $field)
                    <x-select-field :id="'hs-'.$section->id.'-'.$field" :name="$field" :label="$labels[$field][0]" :icon="$labels[$field][1]" :placeholder="$labels[$field][2]" :options="$searchOptions[$field]"/>
                @endforeach
            </div>
            <button class="btn btn-primary mt-4 w-full py-3.5"><x-icon name="search" class="size-4.5"/>{{ $s['search_button'] }}</button>
        </form>
    @endif
</div>
