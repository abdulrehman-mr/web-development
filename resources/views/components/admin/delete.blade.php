@props(['action', 'label' => 'Delete', 'confirm' => 'Delete this item? This cannot be undone.'])
<form method="post" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="inline">
    @csrf @method('DELETE')
    <button {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-500/10']) }}><x-icon name="trash" class="size-4"/>{{ $label }}</button>
</form>
