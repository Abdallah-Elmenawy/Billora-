@if (session('success') || session('error'))
    <div
        id="appFlashData"
        hidden
        data-type="{{ session('success') ? 'success' : 'error' }}"
        data-title="{{ session('success') ? 'تم بنجاح' : 'حدث خطأ' }}"
        data-message="{{ session('success') ?? session('error') }}"
    ></div>
@endif
