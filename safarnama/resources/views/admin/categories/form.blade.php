<x-admin-layout :title="$category->exists ? 'Edit experience' : 'New experience'">
    <form method="post" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="grid max-w-3xl gap-6">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <x-admin.card>
            <div class="grid gap-4">
                <x-admin.input name="name" label="Name" :value="$category->name" required/>
                <x-admin.input name="tagline" label="Tagline" :value="$category->tagline"/>
                <x-admin.textarea name="description" label="Description" :value="$category->description" rows="4"/>
                <x-admin.input name="sort_order" type="number" label="Sort order" :value="$category->sort_order ?? 0"/>
                <x-admin.image name="image" :value="$category->image"/>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Save experience</button></div>
    </form>
</x-admin-layout>
