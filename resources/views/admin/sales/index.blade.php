@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فواتير المبيعات',
        'action' => can('sales.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSaleModal"><i class="fe fe-plus ml-1"></i> إنشاء فاتورة مبيعات جديدة</button>' : '',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'sale'; @endphp

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-3 form-group">
                <label>العميل</label>
                <select name="customer_id" class="form-control">
                    <option value="">كل العملاء</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label>الحالة</label>
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    @foreach(['draft','confirmed','partial','paid','cancelled'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ invoice_status_label($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>رقم الفاتورة</label>
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث برقم الفاتورة...">
            </div>
            <div class="col-md-2 form-group">
                <button class="btn btn-primary btn-block"><i class="fe fe-filter ml-1"></i> تصفية</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-shopping-cart"></i> فواتير المبيعات</h4>
        <span class="count-badge">{{ $invoices->total() }} فاتورة</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>اسم العميل</th>
                    <th>تاريخ البيع</th>
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
                    <td class="cell-strong">{{ $inv->number }}</td>
                    <td>{{ $inv->customer->name ?? '-' }}</td>
                    <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                    <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                    <td class="cell-money">{{ money($inv->total) }}</td>
                    <td class="cell-money {{ $inv->remaining > 0 ? 'is-neg' : '' }}">{{ money($inv->remaining) }}</td>
                    <td>{{ $inv->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($inv->created_at)->format('H:i Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('sales.show', $inv)" title="عرض الفاتورة" />
                            <x-print-link :href="route('sales.print', $inv)" title="طباعة الفاتورة" />
                                @if($inv->isEditable() && can('sales.update'))
                                    <x-edit-link :href="route('sales.edit', $inv)" />
                                @endif
                                @if($inv->isEditable() && can('sales.delete'))
                                    <x-delete-form :action="route('sales.destroy', $inv)" message="هل أنت متأكد من حذف هذه الفاتورة؟" title="حذف الفاتورة" />
                                @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="cell-empty text-center text-muted"><i class="fe fe-file-text"></i>لا توجد فواتير</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} من {{ $invoices->total() }}</span>
            {{ $invoices->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="card mt-3">
    <div class="card-body text-right">
        <h5 class="mb-1">ملخص إجماليات فواتير المبيعات</h5>
        <div class="invoice-summary">
            <div class="invoice-summary-item">
                <strong>إجمالي الفواتير المعروضة حالياً:</strong>
                <span class="invoice-summary-pill is-info">{{ money($displayedTotal) }}</span>
            </div>
            <div class="invoice-summary-item">
                <strong>الإجمالي الكلي لفواتير البيع (غير الملغاة):</strong>
                <span class="invoice-summary-pill is-success">{{ money($grandTotal) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSaleModal" tabindex="-1" role="dialog" aria-labelledby="addSaleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('sales.store') }}">
                @csrf
                <input type="hidden" name="_form" value="sale">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSaleModalTitle">إنشاء فاتورة مبيعات جديدة</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    @include('admin.sales._form-fields', ['fieldId' => 'add_customer_id', 'itemsTableId' => 'add-sale-items'])
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
const saleProducts = @json($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->sale_price]));
document.addEventListener('click', function (e) {
    const addBtn = e.target.closest('.js-add-item');
    if (addBtn) {
        const tbody = document.querySelector('#' + addBtn.dataset.table + ' tbody');
        if (!tbody) return;
        const i = tbody.rows.length;
        const opts = saleProducts.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('');
        const tr = document.createElement('tr');
        tr.innerHTML = `<td class="col-serial"></td><td><select name="items[${i}][product_id]" class="form-control prod">${opts}</select></td>
            <td><input name="items[${i}][qty]" class="form-control" type="number" step="0.001" value="1"></td>
            <td><input name="items[${i}][unit_price]" class="form-control price" type="number" step="0.01" value="${saleProducts[0]?.price||0}"></td>
            <td><input name="items[${i}][discount]" class="form-control" type="number" step="0.01" value="0"></td>
            <td><input name="items[${i}][tax]" class="form-control" type="number" step="0.01" value="0"></td>
            <td class="col-actions"><button type="button" class="btn btn-icon-action btn-icon-delete del" title="حذف البند"><i class="fe fe-trash-2"></i></button></td>`;
        tbody.appendChild(tr);
    }
    const delBtn = e.target.closest('#add-sale-items .del');
    if (delBtn && document.querySelectorAll('#add-sale-items tbody tr').length > 1) {
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
$(function () { $('#addSaleModal').modal('show'); });
@endif
</script>
@endsection
