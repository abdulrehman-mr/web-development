<x-admin-layout title="Site content">
    <x-slot:actions><button form="settings-form" class="btn btn-primary py-2.5">Save changes</button></x-slot:actions>
    <form id="settings-form" method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="grid max-w-4xl gap-6">
        @csrf @method('PUT')
        @foreach ($schema as $group => $fields)
            <x-admin.card :title="$group">
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($fields as $key => [$label, $type, $help])
                        @if ($type === 'image')
                            <x-admin.image :name="$key" :label="$label" :value="setting($key)" :help="$help" class="sm:col-span-2"/>
                        @elseif ($type === 'textarea')
                            <x-admin.textarea :name="$key" :label="$label" :value="setting($key)" :help="$help" rows="4" class="sm:col-span-2"/>
                        @else
                            <x-admin.input :name="$key" :label="$label" :value="setting($key)" :help="$help"/>
                        @endif
                    @endforeach
                </div>
            </x-admin.card>
        @endforeach
        <div><button class="btn btn-primary">Save changes</button></div>
    </form>
</x-admin-layout>
