<x-admin-layout :title="$destination->exists ? 'Edit destination' : 'New destination'">
    <form method="post" enctype="multipart/form-data" action="{{ $destination->exists ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}" class="grid max-w-4xl gap-6">
        @csrf
        @if ($destination->exists) @method('PUT') @endif
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="name" label="Name" :value="$destination->name" required/>
                <div>
                    <label for="f_region" class="label">Region</label>
                    <select id="f_region" name="region" class="field">
                        @foreach (\App\Models\Destination::REGIONS as $r)
                            <option @selected(old('region', $destination->region) === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <x-admin.input name="tagline" label="Tagline" :value="$destination->tagline" class="sm:col-span-2"/>
                <x-admin.textarea name="description" label="Description" :value="$destination->description" rows="5" class="sm:col-span-2"/>
                <x-admin.textarea name="highlights" label="Highlights (one per line)" :value="implode(PHP_EOL, $destination->highlights ?? [])" rows="5"/>
                <div class="space-y-4">
                    <x-admin.input name="best_season" label="Best season" :value="$destination->best_season"/>
                    <x-admin.input name="sort_order" type="number" label="Sort order" :value="$destination->sort_order ?? 0"/>
                    <x-admin.toggle name="is_featured" label="Feature on home page" :checked="$destination->is_featured"/>
                </div>
                <x-admin.image name="image" :value="$destination->image" class="sm:col-span-2"/>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Save destination</button></div>
    </form>
</x-admin-layout>
