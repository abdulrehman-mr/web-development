<x-admin-layout title="Email templates">
    @unless ($mailEnabled)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
            <p><span class="font-semibold">All emails are switched off.</span> No email below will be sent until you turn sending back on.</p>
            <a href="{{ route('admin.email.edit') }}" class="btn btn-ghost py-2">Email settings</a>
        </div>
    @endunless

    <div class="divide-y divide-slate-200 rounded-2xl bg-white ring-1 ring-slate-200">
        @foreach ($templates as $template)
            <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.email-templates.edit', $template) }}" class="font-semibold text-slate-900 hover:underline">{{ $template->name }}</a>
                        <span @class(['rounded-md px-2 py-0.5 text-xs font-semibold', $template->audience === 'admin' ? 'bg-slate-100 text-slate-600' : 'bg-lake-50 text-lake-700'])>{{ $template->audience === 'admin' ? 'To your team' : 'To the guest' }}</span>
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $template->description }}</p>
                    <p class="mt-1 truncate text-xs text-slate-400">Subject: {{ $template->subject }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <form method="post" action="{{ route('admin.email-templates.toggle', $template) }}">
                        @csrf @method('PATCH')
                        <button class="flex items-center gap-2 text-sm" aria-label="{{ $template->is_enabled ? 'Disable' : 'Enable' }} {{ $template->name }}">
                            <span @class(['relative h-6 w-11 rounded-full transition after:absolute after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition', $template->is_enabled ? 'bg-brand-700 after:left-[1.375rem]' : 'bg-slate-300 after:left-0.5'])></span>
                            <span class="w-14 text-left font-medium {{ $template->is_enabled ? 'text-brand-800' : 'text-slate-500' }}">{{ $template->is_enabled ? 'On' : 'Off' }}</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-brand-800 hover:bg-brand-50"><x-icon name="pencil" class="size-4"/>Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
