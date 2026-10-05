@props([
    'href',
    'title' => 'تعديل',
])

<a
    href="{{ $href }}"
    title="{{ $title }}"
    {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-edit']) }}
>
    <i class="fe fe-edit-2"></i>
    <span class="sr-only">{{ $title }}</span>
</a>
