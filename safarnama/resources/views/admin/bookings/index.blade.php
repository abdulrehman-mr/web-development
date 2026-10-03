<x-admin-layout title="Bookings">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="flex gap-1 overflow-x-auto rounded-xl bg-white p-1 text-sm ring-1 ring-marble-200">
            <a href="{{ route('admin.bookings.index') }}" @class(['rounded-lg px-3 py-1.5 whitespace-nowrap', ! $status ? 'bg-pine-800 text-marble-50' : 'text-ink/60 hover:bg-marble-100'])>All</a>
            @foreach (\App\Models\Booking::STATUSES as $s)
                <a href="{{ route('admin.bookings.index', ['status' => $s]) }}" @class(['rounded-lg px-3 py-1.5 capitalize whitespace-nowrap', $status === $s ? 'bg-pine-800 text-marble-50' : 'text-ink/60 hover:bg-marble-100'])>{{ $s }} <span class="opacity-60">{{ $counts[$s] ?? 0 }}</span></a>
            @endforeach
        </nav>
        <form><input type="hidden" name="status" value="{{ $status }}"><input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email or reference" class="field w-full bg-white sm:w-72"></form>
    </div>

    <div class="overflow-x-auto rounded-2xl bg-white ring-1 ring-marble-200">
        <table class="w-full min-w-[52rem] text-left text-sm">
            <thead class="border-b border-marble-200 text-xs text-ink/50"><tr><th class="px-5 py-3 font-medium">Reference</th><th class="py-3 font-medium">Guest</th><th class="py-3 font-medium">Journey</th><th class="py-3 font-medium">Start</th><th class="py-3 font-medium">Pax</th><th class="py-3 font-medium">Estimate</th><th class="py-3 font-medium">Status</th><th class="py-3 pr-5 font-medium">Received</th></tr></thead>
            <tbody class="divide-y divide-marble-200">
                @forelse ($bookings as $b)
                    <tr class="cursor-pointer hover:bg-marble-50" onclick="location='{{ route('admin.bookings.show', $b) }}'">
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-pine-800"><a href="{{ route('admin.bookings.show', $b) }}">{{ $b->reference }}</a></td>
                        <td class="py-3"><span class="font-semibold">{{ $b->full_name }}</span><span class="block text-xs text-ink/50">{{ $b->country }}</span></td>
                        <td class="py-3 text-ink/70">{{ Str::limit($b->package?->title ?? '—', 32) }}</td>
                        <td class="py-3 text-ink/70">{{ $b->start_date?->format('j M Y') }}</td>
                        <td class="py-3 text-ink/70">{{ $b->travelers }}</td>
                        <td class="py-3 text-ink/70">{{ money($b->total_estimate) }}</td>
                        <td class="py-3"><x-admin.status :status="$b->status"/></td>
                        <td class="py-3 pr-5 text-xs text-ink/50">{{ $b->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-ink/50">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $bookings->links() }}</div>
</x-admin-layout>
