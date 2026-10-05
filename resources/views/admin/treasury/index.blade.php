@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الخزينة'])
@endsection
@section('content')
<div class="row">
    @foreach($treasuriesAll as $t)
        <div class="col-md-4"><div class="card"><div class="card-body">
            <h5>{{ $t->name }}</h5>
            <p class="text-muted">{{ $t->type === 'bank' ? 'بنك' : 'صندوق' }}</p>
            <h3>{{ money($t->current_balance) }}</h3>
        </div></div></div>
    @endforeach
</div>
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-header">خزينة جديدة</div><div class="card-body">
            <form method="post" action="{{ route('treasury.store') }}">@csrf
                <input name="name" class="form-control mb-2" placeholder="الاسم" required>
                <select name="type" class="form-control mb-2"><option value="cash">صندوق</option><option value="bank">بنك</option></select>
                <input name="opening_balance" type="number" step="0.01" class="form-control mb-2" placeholder="رصيد أول المدة">
                <div class="form-actions">
                    <button class="btn btn-primary">إضافة</button>
                </div>
            </form>
        </div></div>
        <div class="card"><div class="card-header">تحصيل من عميل</div><div class="card-body">
            <form method="post" action="{{ route('treasury.receipt') }}">@csrf
                <select name="treasury_id" class="form-control mb-2" required>@foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                <select name="customer_id" class="form-control mb-2" required>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                <select name="invoice_id" class="form-control mb-2"><option value="">بدون فاتورة</option>@foreach($saleInvoices as $inv)<option value="{{ $inv->id }}">{{ $inv->number }}</option>@endforeach</select>
                <input name="amount" type="number" step="0.01" class="form-control mb-2" placeholder="المبلغ" required>
                <input name="transacted_at" type="date" class="form-control mb-2" value="{{ now()->toDateString() }}">
                <select name="method" class="form-control mb-2"><option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option></select>
                <div class="form-actions">
                    <button class="btn btn-success">تحصيل</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-header">صرف لمورد</div><div class="card-body">
            <form method="post" action="{{ route('treasury.payment') }}">@csrf
                <select name="treasury_id" class="form-control mb-2" required>@foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
                <select name="supplier_id" class="form-control mb-2" required>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                <select name="invoice_id" class="form-control mb-2"><option value="">بدون فاتورة</option>@foreach($purchaseInvoices as $inv)<option value="{{ $inv->id }}">{{ $inv->number }}</option>@endforeach</select>
                <input name="amount" type="number" step="0.01" class="form-control mb-2" placeholder="المبلغ" required>
                <input name="transacted_at" type="date" class="form-control mb-2" value="{{ now()->toDateString() }}">
                <select name="method" class="form-control mb-2"><option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option></select>
                <div class="form-actions">
                    <button class="btn btn-danger">صرف</button>
                </div>
            </form>
        </div></div>
        <div class="card"><div class="card-header">تحويل بين الخزائن</div><div class="card-body">
            <form method="post" action="{{ route('treasury.transfer') }}">@csrf
                <select name="from_treasury_id" class="form-control mb-2" required>@foreach($treasuries as $t)<option value="{{ $t->id }}">من {{ $t->name }}</option>@endforeach</select>
                <select name="to_treasury_id" class="form-control mb-2" required>@foreach($treasuries as $t)<option value="{{ $t->id }}">إلى {{ $t->name }}</option>@endforeach</select>
                <input name="amount" type="number" step="0.01" class="form-control mb-2" required>
                <input name="transferred_at" type="date" class="form-control mb-2" value="{{ now()->toDateString() }}">
                <div class="form-actions">
                    <button class="btn btn-warning">تحويل</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-header">آخر التحصيلات</div><div class="card-body">
            @foreach($receipts as $r)<div class="mb-2">{{ money($r->amount) }} — {{ $r->party->name ?? '-' }}</div>@endforeach
        </div></div>
        <div class="card"><div class="card-header">آخر الصرف</div><div class="card-body">
            @foreach($payments as $r)<div class="mb-2">{{ money($r->amount) }} — {{ $r->party->name ?? '-' }}</div>@endforeach
        </div></div>
        <div class="card"><div class="card-header">التحويلات</div><div class="card-body">
            @foreach($transfers as $tr)<div class="mb-2">{{ money($tr->amount) }} — {{ $tr->fromTreasury->name ?? '' }} → {{ $tr->toTreasury->name ?? '' }}</div>@endforeach
        </div></div>
    </div>
</div>
@endsection
