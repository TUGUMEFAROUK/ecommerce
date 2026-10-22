@props(['message' => 'Are you sure?', 'variant' => 'danger', 'size' => 'sm', 'label' => null])

<button
    type="submit"
    data-confirm="{{ $message }}"
    {{ $attributes->merge(['class' => 'admin-btn admin-btn-'.$variant.($size === 'sm' ? ' admin-btn-sm' : '')]) }}
>
    {{ $slot->isNotEmpty() ? $slot : $label }}
</button>
