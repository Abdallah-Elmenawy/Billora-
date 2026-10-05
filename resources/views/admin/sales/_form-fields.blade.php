@php
    $partyList = $formCustomers ?? $customers;
    $oldItems = old('items', $invoice->items?->toArray() ?? [['qty' => 1]]);
@endphp
<div class="row">
    <div class="col-md-4 form-group">
        <label for="{{ $fieldId ?? 'customer_id' }}">العميل</label>
        <select id="{{ $fieldId ?? 'customer_id' }}" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
            <option value="">اختر العميل</option>
            @foreach($partyList as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id', $invoice->customer_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('customer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>تاريخ الفاتورة</label>
        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror" required value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d') ?? now()->toDateString()) }}">
        @error('invoice_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 form-group">
        <label>تاريخ الاستحقاق</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered" id="{{ $itemsTableId ?? 'sale-items' }}">
        <thead>
            <tr>
                <th class="col-serial">#</th>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>خصم</th>
                <th>ضريبة</th>
                <th class="col-actions">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
        @foreach($oldItems as $i => $item)
            <tr>
                <td class="col-serial">{{ $loop->iteration }}</td>
                <td>
                    <select name="items[{{ $i }}][product_id]" class="form-control prod" required>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}" @selected(($item['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input name="items[{{ $i }}][qty]" class="form-control" type="number" step="0.001" value="{{ $item['qty'] ?? 1 }}"></td>
                <td><input name="items[{{ $i }}][unit_price]" class="form-control price" type="number" step="0.01" value="{{ $item['unit_price'] ?? 0 }}"></td>
                <td><input name="items[{{ $i }}][discount]" class="form-control" type="number" step="0.01" value="{{ $item['discount'] ?? 0 }}"></td>
                <td><input name="items[{{ $i }}][tax]" class="form-control" type="number" step="0.01" value="{{ $item['tax'] ?? 0 }}"></td>
                <td class="col-actions">
                    <button type="button" class="btn btn-icon-action btn-icon-delete del" title="حذف البند"><i class="fe fe-trash-2"></i></button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<button type="button" class="btn btn-outline-primary btn-sm mb-3 js-add-item" data-table="{{ $itemsTableId ?? 'sale-items' }}">إضافة بند</button>
<div class="row">
    <div class="col-md-4 form-group">
        <label>خصم الفاتورة</label>
        <input name="discount" type="number" step="0.01" class="form-control" value="{{ old('discount', $invoice->discount) }}">
    </div>
    <div class="col-md-8 form-group">
        <label>ملاحظات</label>
        <input name="notes" class="form-control" value="{{ old('notes', $invoice->notes) }}">
    </div>
</div>
@include('admin.partials.invoice-payment-fields', ['paidLabel' => 'المبلغ من العميل'])
@error('items')<div class="text-danger mb-2">{{ $message }}</div>@enderror
