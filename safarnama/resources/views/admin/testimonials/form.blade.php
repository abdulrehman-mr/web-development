<x-admin-layout :title="$testimonial->exists ? 'Edit review' : 'Add review'">
    <form method="post" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="grid max-w-3xl gap-6">
        @csrf
        @if ($testimonial->exists) @method('PUT') @endif
        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input name="name" label="Guest name" :value="$testimonial->name" required/>
                <x-admin.input name="location" label="Location" :value="$testimonial->location" placeholder="Brampton, Ontario"/>
                <x-admin.textarea name="quote" label="Review" :value="$testimonial->quote" rows="5" required class="sm:col-span-2"/>
                <div>
                    <label for="f_rating" class="label">Rating</label>
                    <select id="f_rating" name="rating" class="field">@foreach ([5, 4, 3, 2, 1] as $r)<option value="{{ $r }}" @selected(old('rating', $testimonial->rating) == $r)>{{ $r }} ★</option>@endforeach</select>
                </div>
                <div>
                    <label for="f_package_id" class="label">Journey (optional)</label>
                    <select id="f_package_id" name="package_id" class="field">
                        <option value="">—</option>
                        @foreach ($packages as $p)<option value="{{ $p->id }}" @selected(old('package_id', $testimonial->package_id) == $p->id)>{{ $p->title }}</option>@endforeach
                    </select>
                </div>
                <x-admin.input name="sort_order" type="number" label="Sort order" :value="$testimonial->sort_order ?? 0"/>
                <div class="self-end pb-2"><x-admin.toggle name="is_published" label="Published" :checked="$testimonial->is_published"/></div>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Save review</button></div>
    </form>
</x-admin-layout>
