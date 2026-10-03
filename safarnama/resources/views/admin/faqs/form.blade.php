<x-admin-layout :title="$faq->exists ? 'Edit question' : 'Add question'">
    <form method="post" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="grid max-w-3xl gap-6">
        @csrf
        @if ($faq->exists) @method('PUT') @endif
        <x-admin.card>
            <div class="grid gap-4">
                <x-admin.input name="question" label="Question" :value="$faq->question" required/>
                <x-admin.textarea name="answer" label="Answer" :value="$faq->answer" rows="6" required/>
                <x-admin.input name="sort_order" type="number" label="Sort order" :value="$faq->sort_order ?? 0"/>
                <x-admin.toggle name="is_published" label="Published" :checked="$faq->is_published"/>
            </div>
        </x-admin.card>
        <div><button class="btn btn-primary">Save question</button></div>
    </form>
</x-admin-layout>
