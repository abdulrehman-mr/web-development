<x-admin-layout title="New page">
    <form method="post" action="{{ route('admin.pages.store') }}" class="grid max-w-3xl gap-6" x-data="{ title: @js(old('title', '')), slug: @js(old('slug', '')), touched: {{ old('slug') ? 'true' : 'false' }} }">
        @csrf
        <x-admin.card>
            <div class="grid gap-5">
                <div>
                    <label for="title" class="label">Page title</label>
                    <input id="title" name="title" x-model="title" @input="if (!touched) slug = title.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')" required class="field" placeholder="e.g. Umrah guide">
                </div>
                <div>
                    <label for="slug" class="label">Address</label>
                    <div class="flex items-center rounded-xl border border-slate-300 bg-slate-50 pl-4 text-sm text-slate-500 focus-within:border-brand-600 focus-within:ring-4 focus-within:ring-brand-600/15">
                        <span class="shrink-0">{{ url('/') }}/</span>
                        <input id="slug" name="slug" x-model="slug" @input="touched = true" class="min-w-0 flex-1 rounded-r-xl border-0 bg-white px-2 py-3 text-ink focus:ring-0 focus:outline-none" placeholder="umrah-guide">
                    </div>
                </div>
                <x-admin.textarea name="meta_description" label="Search-engine description (optional)" rows="2"/>
                <fieldset>
                    <legend class="label">Start with</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl p-4 ring-1 ring-slate-200 has-checked:bg-brand-50 has-checked:ring-2 has-checked:ring-brand-600">
                            <input type="radio" name="start" value="starter" checked class="mt-1 accent-brand-700">
                            <span><span class="block text-sm font-semibold">Banner, text and call to action</span><span class="text-xs text-ink/55">A ready layout to fill in.</span></span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-xl p-4 ring-1 ring-slate-200 has-checked:bg-brand-50 has-checked:ring-2 has-checked:ring-brand-600">
                            <input type="radio" name="start" value="empty" class="mt-1 accent-brand-700">
                            <span><span class="block text-sm font-semibold">Empty page</span><span class="text-xs text-ink/55">Add every section yourself.</span></span>
                        </label>
                    </div>
                </fieldset>
                <x-admin.toggle name="is_published" label="Published" help="Drafts can only be seen while you are signed in." :checked="true"/>
                <x-admin.toggle name="show_in_menu" label="Show in the main menu"/>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Create page</button></div>
    </form>
</x-admin-layout>
