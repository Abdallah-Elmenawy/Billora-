@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'مرتجعات المبيعات',
        'action' => can('sales.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSalesReturnModal"><i class="fe fe-plus ml-1"></i> مرتجع جديد</button>' : '',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'sales_return'; @endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-corner-up-left"></i> مرتجعات المبيعات</h4>
        <span class="count-badge">{{ $returns->total() }} مرتجع</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>العميل</th>
                    <th>الفاتورة</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>أنشئت بواسطة</th>
                    <th>تاريخ الإنشاء</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($returns as $r)
                <tr>
                    <td class="col-serial">{{ row_no($returns, $loop) }}</td>
                    <td class="cell-strong">{{ $r->number }}</td>
                    <td>{{ $r->customer->name ?? '-' }}</td>
                    <td>@if($r->invoice)<a href="{{ route('sales.show', $r->invoice) }}">{{ $r->invoice->number }}</a>@else - @endif</td>
                    <td>{{ optional($r->return_date)->format('Y-m-d') }}</td>
                    <td class="cell-money">{{ money($r->total) }}</td>
                    <td><span class="badge badge-{{ invoice_status_badge($r->status) }}">{{ invoice_status_label($r->status) }}</span></td>
                    <td>{{ $r->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($r->created_at)->format('H:i Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('sales-returns.show', $r)" title="عرض المرتجع" />
                                @if($r->status === 'draft' && can('sales.delete'))
                                    <x-delete-form :action="route('sales-returns.destroy', $r)" message="هل أنت متأكد من حذف هذا المرتجع؟" title="حذف المرتجع" />
                                @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="cell-empty text-center text-muted"><i class="fe fe-corner-up-left"></i>لا توجد مرتجعات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $returns->firstItem() }} - {{ $returns->lastItem() }} من {{ $returns->total() }}</span>
            {{ $returns->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addSalesReturnModal" tabindex="-1" role="dialog" aria-labelledby="addSalesReturnModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('sales-returns.store') }}">
                @csrf
                <input type="hidden" name="_form" value="sales_return">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSalesReturnModalTitle">مرتجع مبيعات جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    @if($invoices->isEmpty())
                        <div class="alert alert-warning mb-0">لا توجد فواتير مبيعات مؤكدة يمكن عمل مرتجع عليها.</div>
                    @else
                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label for="sales_invoice_id">الفاتورة</label>
                                <select id="sales_invoice_id" name="sales_invoice_id" class="form-control js-return-invoice" required>
                                    <option value="">اختر الفاتورة</option>
                                    @foreach($invoices as $inv)
                                        <option value="{{ $inv->id }}" @selected(old('sales_invoice_id') == $inv->id)>{{ $inv->number }} — {{ $inv->customer->name ?? '-' }}</option>
                                    @endforeach
                                </select>
                                @error('sales_invoice_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label>تاريخ المرتجع</label>
                                <input type="date" name="return_date" class="form-control" required value="{{ old('return_date', now()->toDateString()) }}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-3">
                                <thead><tr><th class="col-serial">#</th><th>المنتج</th><th>الكمية</th><th>السعر</th></tr></thead>
                                <tbody id="sales-return-items">
                                    <tr><td colspan="4" class="text-muted">اختر فاتورة لعرض البنود</td></tr>
                                </tbody>
                            </table>
                        </div>
                        @error('items')<div class="text-danger mb-2">{{ $message }}</div>@enderror
                        <div class="form-group mb-0">
                            <label>ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    @if($invoices->isNotEmpty())
                        <button type="submit" class="btn btn-primary">حفظ المرتجع</button>
                    @endif
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
const returnInvoices = @json($invoiceItemsJson);
function renderSalesReturnItems(id) {
    const inv = returnInvoices.find(i => String(i.id) === String(id));
    const tbody = document.getElementById('sales-return-items');
    if (!tbody) return;
    if (!inv || !inv.items.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-muted">اختر فاتورة لعرض البنود</td></tr>';
        return;
    }
    tbody.innerHTML = inv.items.map((item, i) => `<tr>
        <td class="col-serial">${i + 1}</td>
        <td>${item.name}<input type="hidden" name="items[${i}][product_id]" value="${item.product_id}"></td>
        <td><input name="items[${i}][qty]" class="form-control" type="number" step="0.001" value="${item.qty}" required></td>
        <td><input name="items[${i}][unit_price]" class="form-control" type="number" step="0.01" value="${item.price}" required></td>
    </tr>`).join('');
}
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-return-invoice')) renderSalesReturnItems(e.target.value);
});
@if($openAddModal)
$(function () {
    $('#addSalesReturnModal').modal('show');
    const selected = document.getElementById('sales_invoice_id');
    if (selected && selected.value) renderSalesReturnItems(selected.value);
});
@endif
</script>
@endsection
