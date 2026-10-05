@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => $customer->name, 'subtitle' => $customer->code])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <p>الهاتف: {{ $customer->phone ?: '-' }}</p>
            <p>البريد: {{ $customer->email ?: '-' }}</p>
            <p>الرصيد الحالي: <strong>{{ money($customer->current_balance) }}</strong></p>
            <p>حد الائتمان: {{ money($customer->credit_limit) }}</p>
            <div class="btn-actions">
                <x-edit-link :href="route('customers.edit', $customer)" />
            </div>
        </div></div>
        <div class="card"><div class="card-header">تحصيل من العميل</div><div class="card-body">
            <form method="post" action="{{ route('customers.collect', $customer) }}">@csrf
                <select name="treasury_id" class="form-control mb-2" required>
                    @foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
                <select name="invoice_id" class="form-control mb-2">
                    <option value="">بدون فاتورة</option>
                    @foreach($customer->invoices->whereIn('status', ['confirmed','partial']) as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->number }} — متبقي {{ money($inv->remaining) }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="المبلغ" required>
                <input type="date" name="transacted_at" class="form-control mb-2" value="{{ now()->toDateString() }}" required>
                <select name="method" class="form-control mb-2">
                    <option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option>
                </select>
                <div class="form-actions">
                    <button class="btn btn-success">تسجيل التحصيل</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card"><div class="card-header">الفواتير</div><div class="card-body table-responsive">
            <table class="table"><thead><tr><th class="col-serial">#</th><th>رقم</th><th>التاريخ</th><th>الإجمالي</th><th>المتبقي</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($customer->invoices as $inv)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td><a href="{{ route('sales.show', $inv) }}">{{ $inv->number }}</a></td>
                    <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                    <td>{{ money($inv->total) }}</td>
                    <td>{{ money($inv->remaining) }}</td>
                    <td>{{ invoice_status_label($inv->status) }}</td>
                </tr>
            @endforeach
            </tbody></table>
        </div></div>
        <div class="card"><div class="card-header">المدفوعات</div><div class="card-body table-responsive">
            <table class="table"><thead><tr><th class="col-serial">#</th><th>المبلغ</th><th>التاريخ</th><th>النوع</th></tr></thead>
            <tbody>
            @foreach($customer->payments as $p)
                <tr><td class="col-serial">{{ $loop->iteration }}</td><td>{{ money($p->amount) }}</td><td>{{ optional($p->transacted_at)->format('Y-m-d') }}</td><td>{{ $p->type }}</td></tr>
            @endforeach
            </tbody></table>
        </div></div>
    </div>
</div>
@endsection
