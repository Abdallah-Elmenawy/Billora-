@props([
    'href',
    'title' => 'عرض',
    'icon' => 'fe-eye',
])

<a
    href="{{ $href }}"
    title="{{ $title }}"
    {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-view']) }}
>
    <i class="fe {{ $icon }}"></i>
    <span class="sr-only">{{ $title }}</span>
</a>
