@props([
    'href' => null,
    'title' => 'طباعة',
])

@if($href)
    <a
        href="{{ $href }}"
        target="_blank"
        rel="noopener"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-print']) }}
    >
        <i class="fe fe-printer"></i>
        <span class="sr-only">{{ $title }}</span>
    </a>
@else
    <button
        type="button"
        onclick="window.print()"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-print']) }}
    >
        <i class="fe fe-printer"></i>
        <span class="sr-only">{{ $title }}</span>
    </button>
@endif
