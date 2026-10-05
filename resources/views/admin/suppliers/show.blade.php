@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => $supplier->name, 'subtitle' => $supplier->code])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <p>الهاتف: {{ $supplier->phone ?: '-' }}</p>
            <p>الرصيد: <strong>{{ money($supplier->current_balance) }}</strong></p>
            <div class="btn-actions">
                <x-edit-link :href="route('suppliers.edit', $supplier)" />
            </div>
        </div></div>
        <div class="card"><div class="card-header">صرف للمورد</div><div class="card-body">
            <form method="post" action="{{ route('suppliers.pay', $supplier) }}">@csrf
                <select name="treasury_id" class="form-control mb-2" required>
                    @foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
                <select name="invoice_id" class="form-control mb-2">
                    <option value="">بدون فاتورة</option>
                    @foreach($supplier->invoices->whereIn('status', ['confirmed','partial']) as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->number }} — متبقي {{ money($inv->remaining) }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="المبلغ" required>
                <input type="date" name="transacted_at" class="form-control mb-2" value="{{ now()->toDateString() }}" required>
                <select name="method" class="form-control mb-2">
                    <option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option>
                </select>
                <div class="form-actions">
                    <button class="btn btn-danger">تسجيل الصرف</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card"><div class="card-header">فواتير المشتريات</div><div class="card-body table-responsive">
            <table class="table"><thead><tr><th class="col-serial">#</th><th>رقم</th><th>التاريخ</th><th>الإجمالي</th><th>المتبقي</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($supplier->invoices as $inv)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td><a href="{{ route('purchases.show', $inv) }}">{{ $inv->number }}</a></td>
                    <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                    <td>{{ money($inv->total) }}</td>
                    <td>{{ money($inv->remaining) }}</td>
                    <td>{{ invoice_status_label($inv->status) }}</td>
                </tr>
            @endforeach
            </tbody></table>
        </div></div>
    </div>
</div>
@endsection
