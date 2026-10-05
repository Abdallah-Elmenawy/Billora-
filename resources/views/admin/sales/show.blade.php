@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'فاتورة '.$invoice->number, 'action' => '<a class="btn btn-secondary" href="'.route('sales.print', $invoice).'" target="_blank">طباعة</a>'])
@endsection
@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card"><div class="card-body">
            <p>العميل: {{ $invoice->customer->name ?? '-' }} — التاريخ: {{ optional($invoice->invoice_date)->format('Y-m-d') }}</p>
            <p>الحالة: <span class="badge badge-{{ invoice_status_badge($invoice->status) }}">{{ invoice_status_label($invoice->status) }}</span></p>
            <table class="table"><thead><tr><th class="col-serial">#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr><td class="col-serial">{{ $loop->iteration }}</td><td>{{ $item->product->name ?? '-' }}</td><td>{{ $item->qty }}</td><td>{{ money($item->unit_price) }}</td><td>{{ money($item->total) }}</td></tr>
            @endforeach
            </tbody></table>
            <p>الإجمالي: {{ money($invoice->total) }} — المدفوع: {{ money($invoice->paid) }} — المتبقي: {{ money($invoice->remaining) }}</p>
            @if($invoice->status==='draft')
                <div class="btn-actions mt-2">
                    <form class="d-inline" method="post" action="{{ route('sales.confirm', $invoice) }}">@csrf<button class="btn btn-success">تأكيد</button></form>
                    <x-edit-link :href="route('sales.edit', $invoice)" />
                    <form class="d-inline" method="post" action="{{ route('sales.cancel', $invoice) }}" data-confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" data-confirm-title="إلغاء الفاتورة" data-confirm-ok="نعم، إلغاء" data-confirm-icon="fe fe-x-circle">@csrf<button class="btn btn-icon-action btn-icon-delete" title="إلغاء"><i class="fe fe-x"></i><span class="sr-only">إلغاء</span></button></form>
                </div>
            @endif
        </div></div>
    </div>
    <div class="col-md-4">
        @if(in_array($invoice->status, ['confirmed','partial']))
        <div class="card"><div class="card-header">تحصيل</div><div class="card-body">
            <form method="post" action="{{ route('sales.pay', $invoice) }}">@csrf
                <select name="treasury_id" class="form-control mb-2" required>
                    @foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
                <input type="number" step="0.01" name="amount" max="{{ $invoice->remaining }}" class="form-control mb-2" value="{{ $invoice->remaining }}" required>
                <input type="date" name="transacted_at" class="form-control mb-2" value="{{ now()->toDateString() }}">
                <div class="form-actions">
                    <button class="btn btn-success">تحصيل</button>
                </div>
            </form>
        </div></div>
        @endif
        <div class="card"><div class="card-header">المدفوعات</div><div class="card-body">
            @foreach($invoice->payments as $p)
                <div>{{ money($p->amount) }} — {{ $p->treasury->name ?? '-' }}</div>
            @endforeach
        </div></div>
    </div>
</div>
@endsection
