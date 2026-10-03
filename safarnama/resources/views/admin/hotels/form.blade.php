<x-admin-layout :title="$hotel->exists ? 'Edit stay' : 'New stay'">
    <form method="post" enctype="multipart/form-data" action="{{ $hotel->exists ? route('admin.hotels.update', $hotel) : route('admin.hotels.store') }}" class="grid max-w-4xl gap-6">
        @csrf
        @if ($hotel->exists) @method('PUT') @endif
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="name" label="Name" :value="$hotel->name" required class="sm:col-span-2"/>
                <div>
                    <label for="f_type" class="label">Type</label>
                    <select id="f_type" name="type" class="field">
                        @foreach (\App\Models\Hotel::TYPES as $t)
                            <option @selected(old('type', $hotel->type) === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_stars" class="label">Stars</label>
                    <select id="f_stars" name="stars" class="field">
                        @foreach ([5, 4, 3, 2, 1] as $s)
                            <option value="{{ $s }}" @selected(old('stars', $hotel->stars) == $s)>{{ $s }} {{ Str::plural('star', $s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="f_destination_id" class="label">Destination</label>
                    <select id="f_destination_id" name="destination_id" class="field">
                        <option value="">—</option>
                        @foreach ($destinations as $d)
                            <option value="{{ $d->id }}" @selected(old('destination_id', $hotel->destination_id) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-admin.input name="address" label="Address" :value="$hotel->address"/>
                <x-admin.textarea name="description" label="Description" :value="$hotel->description" rows="4" class="sm:col-span-2"/>
                <x-admin.textarea name="amenities" label="Amenities (one per line)" :value="implode(PHP_EOL, $hotel->amenities ?? [])" rows="4" class="sm:col-span-2"/>
                <x-admin.image name="image" :value="$hotel->image" class="sm:col-span-2"/>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Save stay</button></div>
    </form>
</x-admin-layout>
