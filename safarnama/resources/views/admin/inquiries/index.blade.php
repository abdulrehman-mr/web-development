<x-admin-layout title="Enquiries">
    <div class="divide-y divide-marble-200 rounded-2xl bg-white ring-1 ring-marble-200">
        @forelse ($inquiries as $q)
            <a href="{{ route('admin.inquiries.show', $q) }}" class="flex gap-4 p-5 hover:bg-marble-50">
                <span @class(['mt-2 size-2 shrink-0 rounded-full', $q->is_read ? 'bg-transparent' : 'bg-brass-500'])></span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                        <p @class(['text-pine-950', $q->is_read ? 'font-medium' : 'font-bold'])>{{ $q->name }} <span class="font-normal text-ink/50">· {{ $q->country }}</span></p>
                        <p class="text-xs text-ink/40">{{ $q->created_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-sm text-brass-700">{{ $q->subject }}</p>
                    <p class="mt-0.5 truncate text-sm text-ink/60">{{ $q->message }}</p>
                </div>
            </a>
        @empty
            <p class="p-12 text-center text-ink/50">No enquiries yet. Messages from the contact form will appear here.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $inquiries->links() }}</div>
</x-admin-layout>
