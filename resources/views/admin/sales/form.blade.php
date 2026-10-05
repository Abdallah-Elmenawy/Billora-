@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل فاتورة مبيعات'])
@endsection
@section('content')
<div class="card">
    <div class="card-body p-4 p-md-5">
        <form method="post" action="{{ route('sales.update', $invoice) }}">
            @csrf
            @method('PUT')
            @include('admin.sales._form-fields', ['itemsTableId' => 'edit-sale-items'])
            <div class="form-actions">
                <button class="btn btn-secondary" name="confirm" value="0">حفظ كمسودة</button>
                <button class="btn btn-primary" name="confirm" value="1">حفظ وتأكيد</button>
                <a href="{{ route('sales.index') }}" class="btn btn-light">رجوع</a>
            </div>
        </form>
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
    const delBtn = e.target.closest('#edit-sale-items .del');
    if (delBtn && document.querySelectorAll('#edit-sale-items tbody tr').length > 1) {
        delBtn.closest('tr').remove();
    }
});
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('prod')) {
        const p = e.target.selectedOptions[0].dataset.price;
        e.target.closest('tr').querySelector('.price').value = p;
    }
});
</script>
@endsection
