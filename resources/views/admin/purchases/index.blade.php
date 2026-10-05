@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فواتير المشتريات',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPurchaseModal"><i class="fe fe-plus ml-1"></i> إنشاء فاتورة مشتريات جديدة</button>',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'purchase'; @endphp

<div class="card mb-3">
    <div class="card-body">
        <form method="get">
            <div class="row align-items-end">
                <div class="col-md-4 form-group mb-md-0">
                    <label>تصفية حسب المورد</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">كل الموردين</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group mb-md-0">
                    <label>تصفية حسب الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">كل الحالات</option>
                        @foreach(['draft','confirmed','partial','paid','cancelled'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ invoice_status_label($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group mb-md-0">
                    <label>رقم الفاتورة</label>
                    <div class="d-flex">
                        <input name="q" value="{{ request('q') }}" class="form-control ml-2" placeholder="بحث">
                        <button class="btn btn-primary">تصفية</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>الرقم</th>
                        <th>اسم المورد</th>
                        <th>تاريخ الشراء</th>
                        <th>الحالة</th>
                        <th>الإجمالي</th>
                        <th>المتبقي</th>
                        <th>أنشئت بواسطة</th>
                        <th>تاريخ الإنشاء</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="col-serial">{{ row_no($invoices, $loop) }}</td>
                        <td>{{ $inv->number }}</td>
                        <td>{{ $inv->supplier->name ?? '-' }}</td>
                        <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                        <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                        <td>{{ money($inv->total) }}</td>
                        <td>{{ money($inv->remaining) }}</td>
                        <td>{{ $inv->creator->name ?? 'غير محدد' }}</td>
                        <td>{{ optional($inv->created_at)->format('H:i Y-m-d') }}</td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <a href="{{ route('purchases.show', $inv) }}" class="btn btn-icon-action btn-icon-view" title="البنود">
                                    <i class="fe fe-eye"></i><span class="sr-only">البنود</span>
                                </a>
                                @if($inv->isEditable())
                                    <x-edit-link :href="route('purchases.edit', $inv)" />
                                    <x-delete-form :action="route('purchases.destroy', $inv)" message="هل أنت متأكد من حذف هذه الفاتورة؟" title="حذف الفاتورة" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">لا توجد فواتير</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body text-right">
        <h5 class="mb-1">ملخص إجماليات فواتير المشتريات</h5>
        <div class="invoice-summary">
            <div class="invoice-summary-item">
                <strong>إجمالي الفواتير المعروضة حالياً:</strong>
                <span class="invoice-summary-pill is-info">{{ money($displayedTotal) }}</span>
            </div>
            <div class="invoice-summary-item">
                <strong>الإجمالي الكلي لفواتير الشراء (غير الملغاة):</strong>
                <span class="invoice-summary-pill is-success">{{ money($grandTotal) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPurchaseModal" tabindex="-1" role="dialog" aria-labelledby="addPurchaseModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('purchases.store') }}">
                @csrf
                <input type="hidden" name="_form" value="purchase">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPurchaseModalTitle">إنشاء فاتورة مشتريات جديدة</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    @include('admin.purchases._form-fields', ['fieldId' => 'add_supplier_id', 'itemsTableId' => 'add-purchase-items'])
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button class="btn btn-secondary" name="confirm" value="0">حفظ كمسودة</button>
                    <button class="btn btn-primary" name="confirm" value="1">حفظ وتأكيد</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
const purchaseProducts = @json($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->cost_price]));
document.addEventListener('click', function (e) {
    const addBtn = e.target.closest('.js-add-item');
    if (addBtn) {
        const tbody = document.querySelector('#' + addBtn.dataset.table + ' tbody');
        if (!tbody) return;
        const i = tbody.rows.length;
        const opts = purchaseProducts.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="col-serial"></td><td><select name="items[${i}][product_id]" class="form-control prod">${opts}</select></td>
            <td><input name="items[${i}][qty]" class="form-control" type="number" step="0.001" value="1"></td>
            <td><input name="items[${i}][unit_cost]" class="form-control price" type="number" step="0.01" value="${purchaseProducts[0]?.price||0}"></td>
            <td><input name="items[${i}][discount]" class="form-control" type="number" step="0.01" value="0"></td>
            <td><input name="items[${i}][tax]" class="form-control" type="number" step="0.01" value="0"></td>
            <td class="col-actions"><button type="button" class="btn btn-icon-action btn-icon-delete del" title="حذف البند"><i class="fe fe-trash-2"></i></button></td>`;
        tbody.appendChild(tr);
    }
    const delBtn = e.target.closest('#add-purchase-items .del');
    if (delBtn && document.querySelectorAll('#add-purchase-items tbody tr').length > 1) {
        delBtn.closest('tr').remove();
    }
});
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('prod')) {
        const p = e.target.selectedOptions[0].dataset.price;
        e.target.closest('tr').querySelector('.price').value = p;
    }
});
@if($openAddModal)
$(function () { $('#addPurchaseModal').modal('show'); });
@endif
</script>
@endsection
