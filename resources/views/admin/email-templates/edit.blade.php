<x-admin-layout :title="$template->name">
    <x-slot:actions>
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-ghost py-2.5">All templates</a>
        <button form="template-form" class="btn btn-primary py-2.5">Save</button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-2" x-data="templateEditor(@js(route('admin.email-templates.preview', $template)))">
        <div class="min-w-0 space-y-6">
            <form id="template-form" method="post" action="{{ route('admin.email-templates.update', $template) }}">
                @csrf @method('PUT')
                <x-admin.card :description="$template->description.' '.($template->audience === 'admin' ? 'Goes to your team notification address.' : 'Goes to the guest.')">
                    <x-admin.toggle name="is_enabled" label="Send this email" :checked="$template->is_enabled"/>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="subject" class="label">Subject</label>
                            <input id="subject" name="subject" x-ref="subject" value="{{ old('subject', $template->subject) }}" @input.debounce.400ms="refresh()" @focus="target = $el" class="field" required>
                        </div>
                        <div>
                            <label for="body" class="label">Message</label>
                            <textarea id="body" name="body" x-ref="body" rows="16" @input.debounce.400ms="refresh()" @focus="target = $el" class="field font-mono text-[13px] leading-relaxed" required>{{ old('body', $template->body) }}</textarea>
                            <p class="mt-1.5 text-xs text-slate-500">
                                <strong>**bold**</strong> · <em>*italic*</em> · <code>- list item</code> · <code>&gt; quote</code> · <code>[link text](https://…)</code> ·
                                Button: <code>[[Pay securely|{payment_link}]]</code> · end a line with two spaces for a line break.
                            </p>
                        </div>
                    </div>
                </x-admin.card>
            </form>

            <x-admin.card title="Placeholders" description="Click to insert at the cursor. They are replaced with the real details when the email is sent.">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($placeholders as $key => $hint)
                        <button type="button" @click="insert('{{ '{'.$key.'}' }}')" title="{{ $hint }}" class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700 hover:bg-brand-50 hover:text-brand-800">{{ '{'.$key.'}' }}</button>
                    @endforeach
                </div>
            </x-admin.card>

            <div class="flex flex-wrap gap-3">
                <form method="post" action="{{ route('admin.email-templates.test', $template) }}" class="flex flex-1 flex-wrap gap-2">
                    @csrf
                    <input type="email" name="test_to" value="{{ auth()->user()->email }}" class="field min-w-0 flex-1" required aria-label="Send test to">
                    <button class="btn btn-ghost"><x-icon name="mail" class="size-4"/>Send test</button>
                    @error('test_to')<p class="w-full text-xs text-rose-600">{{ $message }}</p>@enderror
                </form>
                @if ($hasDefault)
                    <form method="post" action="{{ route('admin.email-templates.reset', $template) }}" onsubmit="return confirm('Replace the subject and message with the default wording?')">
                        @csrf
                        <button class="btn btn-ghost text-rose-600">Reset to default</button>
                    </form>
                @endif
            </div>
            <p class="text-xs text-slate-500">Tests use sample details and the saved version of this template.</p>
        </div>

        <div class="min-w-0">
            <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200 xl:sticky xl:top-24">
                <div class="border-b border-slate-200 px-5 py-3">
                    <p class="text-xs text-slate-500">Preview with sample details <span x-show="loading" class="text-slate-400">· updating…</span></p>
                    <p class="mt-0.5 truncate font-semibold text-slate-900" x-text="subject">{{ $template->subject }}</p>
                </div>
                <iframe x-ref="frame" title="Email preview" class="h-[44rem] w-full bg-slate-100" sandbox=""></iframe>
            </div>
        </div>
    </div>

    <script>
        function templateEditor(previewUrl) {
            return {
                subject: '', loading: false, target: null,
                init() { this.target = this.$refs.body; this.refresh(); },
                insert(text) {
                    const el = this.target ?? this.$refs.body;
                    const start = el.selectionStart ?? el.value.length, end = el.selectionEnd ?? start;
                    el.value = el.value.slice(0, start) + text + el.value.slice(end);
                    el.focus();
                    el.selectionStart = el.selectionEnd = start + text.length;
                    this.refresh();
                },
                async refresh() {
                    this.loading = true;
                    const body = new FormData();
                    body.append('subject', this.$refs.subject.value);
                    body.append('body', this.$refs.body.value);
                    try {
                        const res = await fetch(previewUrl, { method: 'POST', body, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } });
                        if (res.ok) {
                            this.$refs.frame.srcdoc = await res.text();
                            this.subject = decodeURIComponent(res.headers.get('X-Email-Subject') ?? '');
                        }
                    } finally { this.loading = false; }
                },
            };
        }
    </script>
</x-admin-layout>
