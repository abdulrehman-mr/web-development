<x-admin-layout title="Content packs">
    <div class="mb-6 rounded-2xl bg-white p-5 text-sm text-slate-600 ring-1 ring-slate-200">
        <p class="font-semibold text-slate-900">Add a new country in one click</p>
        <p class="mt-1">Each pack adds destinations, hotels and complete journeys — itineraries, prices (converted from USD to {{ currencies()->base()->code }}), group prices, room and hotel choices, extras and vibes. Photos come from Wikipedia and download in the background. Importing twice never duplicates anything, and everything stays editable.</p>
        <p class="mt-2 text-xs text-slate-500">Pack hotels, prices and itineraries are sample content — review them before you sell.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($packs as $pack)
            @php $done = array_sum($pack['imported']) > 0; @endphp
            <article class="flex flex-col rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-bold text-slate-900">{{ $pack['name'] }}</p>
                        <p class="text-xs text-slate-500">{{ $pack['country'] }} · {{ count($pack['destinations']) }} destinations · {{ count($pack['hotels']) }} hotels · {{ count($pack['packages']) }} journeys</p>
                    </div>
                    @if ($done)<span class="shrink-0 rounded-md bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-800">Imported</span>@endif
                </div>
                <p class="mt-3 text-sm text-slate-600">{{ $pack['summary'] }}</p>
                <ul class="mt-3 space-y-1 text-sm text-slate-700">
                    @foreach ($pack['packages'] as $journey)
                        <li class="flex gap-2"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-brand-600"/><span>{{ $journey['title'] }} <span class="text-slate-400">· {{ count($journey['itinerary']) }} days</span></span></li>
                    @endforeach
                </ul>
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach (collect($pack['packages'])->pluck('vibes')->flatten()->unique()->take(6) as $vibe)
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $vibe }}</span>
                    @endforeach
                </div>
                <div class="mt-auto pt-5">
                    @if ($done)
                        <p class="mb-3 text-xs text-slate-500">On your site: {{ $pack['imported']['packages'] }} journeys, {{ $pack['imported']['destinations'] }} destinations, {{ $pack['imported']['hotels'] }} hotels.</p>
                    @endif
                    <form method="post" action="{{ route('admin.content-packs.import', $pack['key']) }}" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="publish" value="1" checked class="size-4 accent-brand-700"> Publish journeys</label>
                        <button class="btn btn-primary ml-auto py-2">{{ $done ? 'Import missing items' : 'Import pack' }}</button>
                    </form>
                    @if ($done)
                        <form method="post" action="{{ route('admin.content-packs.destroy', $pack['key']) }}" class="mt-2 text-right" onsubmit="return confirm('Remove everything this pack added? Journeys with bookings are kept but unpublished.')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold text-rose-600 hover:underline">Remove pack</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</x-admin-layout>
