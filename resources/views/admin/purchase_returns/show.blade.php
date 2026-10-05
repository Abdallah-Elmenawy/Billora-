@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'مرتجع '.$return->number])
@endsection
@section('content')
<div class="card"><div class="card-body">
<p>المورد: {{ $return->supplier->name }} — الفاتورة: {{ $return->invoice->number ?? '-' }}</p>
<p>الحالة: {{ invoice_status_label($return->status) }} — الإجمالي: {{ money($return->total) }}</p>
<table class="table"><thead><tr><th class="col-serial">#</th><th>المنتج</th><th>كمية</th><th>سعر</th><th>إجمالي</th></tr></thead>
<tbody>
@foreach($return->items as $item)
    <tr><td class="col-serial">{{ $loop->iteration }}</td><td>{{ $item->product->name ?? '-' }}</td><td>{{ $item->qty }}</td><td>{{ money($item->unit_price) }}</td><td>{{ money($item->total) }}</td></tr>
@endforeach
</tbody></table>
@if($return->status==='draft')
<form method="post" action="{{ route('purchase-returns.confirm', $return) }}">@csrf<button class="btn btn-success">تأكيد</button></form>
@endif
</div></div>
@endsection
