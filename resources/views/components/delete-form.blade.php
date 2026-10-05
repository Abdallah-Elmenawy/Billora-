@props([
    'action',
    'message' => 'هل أنت متأكد من الحذف؟ لا يمكن التراجع عن هذا الإجراء.',
    'title' => 'تأكيد الحذف',
    'ok' => 'نعم، احذف',
    'buttonTitle' => 'حذف',
])

<form
    method="post"
    action="{{ $action }}"
    class="d-inline-flex align-items-center m-0"
    data-confirm="{{ $message }}"
    data-confirm-title="{{ $title }}"
    data-confirm-ok="{{ $ok }}"
>
    @csrf
    @method('DELETE')
    <button
        {{ $attributes->merge([
            'type' => 'submit',
            'class' => 'btn btn-icon-action btn-icon-delete',
            'title' => $buttonTitle,
        ]) }}
    >
        @if ($slot->isEmpty())
            <i class="fe fe-trash-2"></i>
            <span class="sr-only">{{ $buttonTitle }}</span>
        @else
            {{ $slot }}
        @endif
    </button>
</form>
