<x-admin-layout title="Dashboard">
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach ($stats as [$label, $value, $url])
            <a href="{{ $url }}" class="rounded-2xl bg-white p-5 ring-1 ring-marble-200 transition hover:ring-pine-600">
                <p class="text-sm text-ink/55">{{ $label }}</p>
                <p class="mt-2 font-display text-4xl font-semibold text-pine-900">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-4 rounded-2xl bg-pine-900 p-5 text-marble-50 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-marble-50/60">Open pipeline (pending + confirmed)</p>
            <p class="mt-1 font-display text-4xl font-semibold">{{ money($pipeline) }} <span class="text-base font-normal text-marble-50/50">USD</span></p>
        </div>
        <div class="mt-4 flex flex-wrap gap-2 sm:mt-0">
            <a href="{{ route('admin.packages.create') }}" class="btn btn-brass py-2.5"><x-icon name="plus" class="size-4"/>New journey</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-ghost-light py-2.5">Edit site content</a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Latest booking requests" class="xl:col-span-2">
            @if ($recentBookings->isEmpty())
                <p class="text-sm text-ink/50">No bookings yet. They will appear here as soon as a visitor requests a journey.</p>
            @else
                <div class="-mx-5 overflow-x-auto sm:-mx-6">
                    <table class="w-full min-w-[36rem] text-left text-sm">
                        <thead class="text-xs text-ink/50"><tr><th class="px-5 py-2 font-medium sm:px-6">Guest</th><th class="py-2 font-medium">Journey</th><th class="py-2 font-medium">Start</th><th class="py-2 font-medium">Status</th></tr></thead>
                        <tbody class="divide-y divide-marble-200">
                            @foreach ($recentBookings as $b)
                                <tr class="hover:bg-marble-50">
                                    <td class="px-5 py-3 sm:px-6"><a href="{{ route('admin.bookings.show', $b) }}" class="font-semibold text-pine-900 hover:underline">{{ $b->full_name }}</a><span class="block text-xs text-ink/50">{{ $b->reference }} · {{ $b->country }}</span></td>
                                    <td class="py-3 text-ink/70">{{ Str::limit($b->package?->title, 34) }}</td>
                                    <td class="py-3 text-ink/70">{{ $b->start_date?->format('j M Y') }}</td>
                                    <td class="py-3"><x-admin.status :status="$b->status"/></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
        <x-admin.card title="Latest enquiries">
            <ul class="-my-2 divide-y divide-marble-200">
                @forelse ($recentInquiries as $q)
                    <li class="py-3">
                        <a href="{{ route('admin.inquiries.show', $q) }}" class="flex items-center gap-2 font-semibold text-pine-900 hover:underline">@unless($q->is_read)<span class="size-2 rounded-full bg-brass-500"></span>@endunless{{ $q->name }}</a>
                        <p class="truncate text-sm text-ink/60">{{ $q->message }}</p>
                        <p class="text-xs text-ink/40">{{ $q->created_at->diffForHumans() }}</p>
                    </li>
                @empty
                    <li class="py-2 text-sm text-ink/50">No enquiries yet.</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
