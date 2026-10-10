<x-admin-layout title="Enquiry">
    <x-slot:actions><x-admin.delete :action="route('admin.inquiries.destroy', $inquiry)"/></x-slot:actions>
    <div class="max-w-3xl">
        <x-admin.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $inquiry->name }}</p>
                    <p class="text-sm text-ink/60">{{ $inquiry->email }} @if($inquiry->phone) · {{ $inquiry->phone }} @endif @if($inquiry->country) · {{ $inquiry->country }} @endif</p>
                </div>
                <p class="text-xs text-ink/40">{{ $inquiry->created_at->format('j M Y, g:ia') }}</p>
            </div>
            @if ($inquiry->subject)<p class="mt-4 text-sm font-semibold text-lake-700">{{ $inquiry->subject }}</p>@endif
            <p class="mt-2 leading-relaxed whitespace-pre-line text-ink/80">{{ $inquiry->message }}</p>
            <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-200 pt-5">
                <a href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Re: '.($inquiry->subject ?: 'Your enquiry')) }}" class="btn btn-primary py-2.5"><x-icon name="mail" class="size-4"/>Reply by email</a>
                @if ($inquiry->phone)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $inquiry->phone) }}" target="_blank" rel="noopener" class="btn btn-ghost py-2.5"><x-icon name="chat" class="size-4"/>WhatsApp</a>
                @endif
                <a href="{{ route('admin.inquiries.index') }}" class="btn py-2.5 text-ink/60">Back to enquiries</a>
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
