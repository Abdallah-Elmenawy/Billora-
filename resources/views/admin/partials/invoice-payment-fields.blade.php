@php
    $payStatus = old('payment_status', 'paid');
    $treasuries = $treasuries ?? collect();
@endphp
<div class="row invoice-pay-fields">
    <div class="col-md-4 form-group">
        <label>حالة الفاتورة</label>
        <select name="payment_status" class="form-control js-payment-status">
            <option value="paid" @selected($payStatus === 'paid')>مدفوع</option>
            <option value="unpaid" @selected($payStatus === 'unpaid')>غير مدفوع</option>
        </select>
    </div>
    <div class="col-md-4 form-group js-paid-wrap" @if($payStatus === 'unpaid') hidden @endif>
        <label>{{ $paidLabel }}</label>
        <input name="paid_amount" type="number" min="0" step="0.01" class="form-control" value="{{ old('paid_amount', 0) }}">
    </div>
    <div class="col-md-4 form-group js-treasury-wrap" @if($payStatus === 'unpaid') hidden @endif>
        <label>الخزينة</label>
        <select name="treasury_id" class="form-control">
            @foreach($treasuries as $treasury)
                <option value="{{ $treasury->id }}" @selected(old('treasury_id', $treasuries->first()?->id) == $treasury->id)>{{ $treasury->name }}</option>
            @endforeach
        </select>
        @if($treasuries->isEmpty())
            <small class="text-danger">لا توجد خزينة نشطة للتحصيل أو الصرف.</small>
        @endif
    </div>
</div>
@once
<script>
function invoiceFormTotal(form) {
    let total = 0;
    form.querySelectorAll('table tbody tr').forEach(function (tr) {
        const qty = Number((tr.querySelector('[name*="[qty]"]') || {}).value || 0);
        const price = Number((tr.querySelector('.price') || {}).value || 0);
        const tax = Number((tr.querySelector('[name*="[tax]"]') || {}).value || 0);
        const discounts = tr.querySelectorAll('[name*="[discount]"]');
        const disc = Number((discounts[0] || {}).value || 0);
        total += (qty * price) - disc + tax;
    });
    const headerDisc = Number((form.querySelector('input[name="discount"]') || {}).value || 0);
    return Math.max(total - headerDisc, 0);
}
function syncPaidAmount(form) {
    const status = form.querySelector('.js-payment-status');
    const amount = form.querySelector('[name="paid_amount"]');
    if (!status || !amount || status.value !== 'paid') return;
    if (!amount.value || Number(amount.value) === 0) {
        amount.value = invoiceFormTotal(form).toFixed(2);
    }
}
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('js-payment-status')) return;
    const form = e.target.closest('form');
    if (!form) return;
    const paid = e.target.value === 'paid';
    form.querySelectorAll('.js-paid-wrap, .js-treasury-wrap').forEach(function (el) {
        el.hidden = !paid;
    });
    const amount = form.querySelector('[name="paid_amount"]');
    if (paid) syncPaidAmount(form);
    if (!paid && amount) amount.value = 0;
});
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button[name="confirm"][value="1"]');
    if (!btn) return;
    const form = btn.closest('form');
    if (form) syncPaidAmount(form);
});
</script>
@endonce
