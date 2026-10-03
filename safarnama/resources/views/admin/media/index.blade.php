<x-admin-layout title="Media library">
    <div x-data="mediaImport(@js(route('admin.media.import')), @js($stats))" class="space-y-6">
        {{-- Status + import --}}
        <div class="grid gap-4 lg:grid-cols-3">
            <x-admin.card class="lg:col-span-2" title="Import images to this server" description="Copies remote images (e.g. Wikimedia, Unsplash, your old website) into local storage and switches the site to the local copies. It also runs automatically on deploy via npm install and php artisan images:import.">
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach (['pending' => 'Waiting', 'imported' => 'Imported', 'failed' => 'Failed', 'missing' => 'Missing files'] as $key => $label)
                        <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                            <dt class="text-xs text-slate-500">{{ $label }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-slate-900" x-text="stats.{{ $key }}">{{ $stats[$key] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn btn-primary" @click="run(false)" :disabled="running">
                        <x-icon name="inbox" class="size-4"/><span x-text="running ? 'Importing…' : 'Import now'">Import now</span>
                    </button>
                    <button type="button" class="btn btn-ghost" @click="run(true)" :disabled="running" x-show="stats.failed > 0">Retry failed</button>
                    <form method="post" action="{{ route('admin.media.scan') }}">@csrf<button class="btn btn-ghost" :disabled="running">Scan site content</button></form>
                </div>

                <div x-cloak x-show="log.length" class="mt-5">
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600 transition-all" :style="`width: ${progress}%`"></div></div>
                    <p class="mt-2 text-sm text-slate-600" x-text="message"></p>
                    <ul class="mt-3 max-h-56 space-y-1 overflow-y-auto rounded-xl bg-slate-50 p-3 font-mono text-xs">
                        <template x-for="item in log" :key="item.id">
                            <li class="flex gap-2"><span :class="item.ok ? 'text-brand-700' : 'text-rose-600'" x-text="item.ok ? '✓' : '✗'"></span><span class="min-w-0 truncate text-slate-700" x-text="item.url"></span><span class="shrink-0 text-rose-600" x-show="!item.ok" x-text="item.error"></span></li>
                        </template>
                    </ul>
                </div>
            </x-admin.card>

            <x-admin.card title="Add image URLs" description="One URL per line. They are added to the library and downloaded on the next import.">
                <form method="post" action="{{ route('admin.media.store') }}" class="space-y-3">
                    @csrf
                    <textarea name="urls" rows="6" class="field font-mono text-xs" placeholder="https://commons.wikimedia.org/wiki/Special:FilePath/Attabad_Lake,_Hunza.jpg?width=1600"></textarea>
                    <button class="btn btn-ghost w-full">Add to library</button>
                </form>
            </x-admin.card>
        </div>

        {{-- Library --}}
        <div>
            <nav class="mb-4 flex gap-1 overflow-x-auto rounded-xl bg-white p-1 text-sm ring-1 ring-slate-200 sm:inline-flex">
                @foreach (['' => 'All', 'imported' => 'Imported', 'pending' => 'Waiting', 'failed' => 'Failed'] as $key => $label)
                    <a href="{{ route('admin.media.index', array_filter(['status' => $key])) }}" @class(['rounded-lg px-3 py-1.5 whitespace-nowrap', ($status ?? '') === $key ? 'bg-brand-700 text-white' : 'text-slate-600 hover:bg-slate-100'])>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($assets->isEmpty())
                <div class="rounded-2xl bg-white p-12 text-center text-slate-500 ring-1 ring-slate-200">No images here yet. Add URLs or scan the site content.</div>
            @else
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($assets as $asset)
                        <figure class="min-w-0 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200" x-data="{ copied: false }">
                            <x-media :src="$asset->fileExists() ? $asset->url : null" class="aspect-[4/3]">
                                <span @class(['absolute top-2 left-2 rounded-md px-2 py-0.5 text-xs font-semibold capitalize',
                                    'bg-brand-100 text-brand-800' => $asset->status === 'imported',
                                    'bg-lake-50 text-lake-700' => $asset->status === 'pending',
                                    'bg-rose-100 text-rose-700' => $asset->status === 'failed'])>{{ $asset->status === 'pending' ? 'waiting' : $asset->status }}</span>
                            </x-media>
                            <figcaption class="space-y-2 p-3 text-xs">
                                <a href="{{ $asset->source_url }}" target="_blank" rel="noopener" class="block truncate text-slate-500 hover:text-slate-900" title="{{ $asset->source_url }}">{{ $asset->source_url }}</a>
                                @if ($asset->error)<p class="line-clamp-2 text-rose-600" title="{{ $asset->error }}">{{ $asset->error }}</p>@endif
                                <div class="flex items-center justify-between gap-2">
                                    @if ($asset->fileExists())
                                        <button type="button" class="font-semibold text-brand-700 hover:underline" @click="navigator.clipboard.writeText(@js($asset->path)); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied!' : 'Copy local path'">Copy local path</button>
                                        <span class="text-slate-400">{{ $asset->bytes ? number_format($asset->bytes / 1024).' KB' : '' }}</span>
                                    @else
                                        <span class="text-slate-400">Not downloaded yet</span>
                                    @endif
                                </div>
                                <x-admin.delete :action="route('admin.media.destroy', $asset)" label="Remove" confirm="Remove this image from the library? Content that still uses its URL will load it from the original site." class="-ml-2.5"/>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                <div class="mt-6">{{ $assets->links() }}</div>
            @endif
        </div>
    </div>

    <script>
        function mediaImport(endpoint, stats) {
            return {
                stats, running: false, log: [], message: '', done: 0, total: 0,
                get progress() { return this.total ? Math.round(this.done / this.total * 100) : 0 },
                async run(retryFailed) {
                    this.running = true; this.log = []; this.done = 0;
                    this.total = retryFailed ? this.stats.pending + this.stats.failed + this.stats.missing : this.stats.pending + this.stats.missing;
                    this.message = 'Starting…';
                    const skip = [];
                    let first = true;
                    try {
                        while (true) {
                            const res = await fetch(endpoint, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                body: JSON.stringify({ retry_failed: retryFailed, first, skip }),
                            });
                            if (!res.ok) throw new Error('Server responded ' + res.status);
                            const data = await res.json();
                            first = false;
                            data.results.forEach(r => { this.log.unshift(r); skip.push(r.id); });
                            this.done += data.results.length;
                            this.total = Math.max(this.total, this.done + data.remaining);
                            this.stats = data.stats;
                            this.message = `${this.done} processed, ${data.remaining} remaining…`;
                            if (data.remaining === 0 || data.results.length === 0) {
                                const ok = this.log.filter(r => r.ok).length, bad = this.log.length - ok;
                                this.message = `Finished: ${ok} imported, ${bad} failed.`
                                    + (ok ? ' The site now uses the local copies.' : '')
                                    + (bad ? ' Check the failed items below: fix or replace their URLs, then use “Retry failed”.' : '');
                                break;
                            }
                        }
                    } catch (e) {
                        this.message = 'Import stopped: ' + e.message + '. You can run it again — finished images are kept.';
                    }
                    this.running = false;
                },
            };
        }
    </script>
</x-admin-layout>
