@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'مرتجع مشتريات'])
@endsection
@section('content')
<div class="card"><div class="card-body">
@if(!$invoice)
    <form method="get">
        <select name="invoice_id" class="form-control mb-2" required>
            @foreach($invoices as $inv)<option value="{{ $inv->id }}">{{ $inv->number }} — {{ $inv->supplier->name }}</option>@endforeach
        </select>
        <button class="btn btn-secondary">اختيار الفاتورة</button>
    </form>
@else
    <form method="post" action="{{ route('purchase-returns.store') }}">@csrf
        <input type="hidden" name="purchase_invoice_id" value="{{ $invoice->id }}">
        <p>فاتورة {{ $invoice->number }} — {{ $invoice->supplier->name }}</p>
        <input type="date" name="return_date" class="form-control mb-2" value="{{ now()->toDateString() }}">
        <table class="table">
            <thead><tr><th class="col-serial">#</th><th>المنتج</th><th>الكمية</th><th>السعر</th></tr></thead>
            <tbody>
            @foreach($invoice->items as $i => $item)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name }}<input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}"></td>
                    <td><input name="items[{{ $i }}][qty]" class="form-control" type="number" step="0.001" value="{{ $item->qty }}"></td>
                    <td><input name="items[{{ $i }}][unit_price]" class="form-control" type="number" step="0.01" value="{{ $item->unit_price }}"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <textarea name="notes" class="form-control mb-2"></textarea>
        <button class="btn btn-primary">حفظ المرتجع</button>
    </form>
@endif
</div></div>
@endsection
