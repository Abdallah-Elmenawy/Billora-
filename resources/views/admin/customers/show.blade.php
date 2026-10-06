@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $customer->name,
        'subtitle' => $customer->code,
        'action' => '<a href="'.route('customers.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل العملاء</a>
                     <a href="'.route('customers.edit', $customer).'" class="btn btn-primary"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>
                     <button type="button" class="btn btn-light" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><i class="fe fe-user"></i> بيانات العميل</h4></div>
            <div class="card-body">
                <div class="info-row"><span>الهاتف</span><strong dir="ltr">{{ $customer->phone ?: '-' }}</strong></div>
                <div class="info-row"><span>البريد</span><strong dir="ltr">{{ $customer->email ?: '-' }}</strong></div>
                <div class="info-row"><span>العنوان</span><strong>{{ $customer->address ?: '-' }}</strong></div>
                <div class="info-row"><span>حد الائتمان</span><strong>{{ money($customer->credit_limit) }}</strong></div>
                <div class="info-row is-total"><span>الرصيد الحالي</span><strong class="{{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">{{ money($customer->current_balance) }}</strong></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title"><i class="fe fe-download"></i> تحصيل من العميل</h4></div>
            <div class="card-body">
                <form method="post" action="{{ route('customers.collect', $customer) }}">@csrf
                    <div class="form-group">
                        <label>الخزينة</label>
                        <select name="treasury_id" class="form-control" required>
                            @foreach($treasuries as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الفاتورة</label>
                        <select name="invoice_id" class="form-control">
                            <option value="">بدون فاتورة</option>
                            @foreach($customer->invoices->whereIn('status', ['confirmed','partial']) as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->number }} — متبقي {{ money($inv->remaining) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>المبلغ</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-6 form-group">
                            <label>التاريخ</label>
                            <input type="date" name="transacted_at" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>طريقة الدفع</label>
                        <select name="method" class="form-control">
                            <option value="cash">نقدي</option><option value="bank">بنك</option><option value="transfer">تحويل</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-success btn-block"><i class="fe fe-download ml-1"></i> تسجيل التحصيل</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fe fe-file-text"></i> الفواتير</h4>
                <span class="count-badge">{{ $customer->invoices->count() }} فاتورة</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>الرقم</th><th>التاريخ</th><th>الإجمالي</th><th>المتبقي</th><th>الحالة</th><th class="col-actions">الإجراءات</th></tr></thead>
                    <tbody>
                    @forelse($customer->invoices as $inv)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td class="cell-strong"><a href="{{ route('sales.show', $inv) }}">{{ $inv->number }}</a></td>
                            <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                            <td class="cell-money">{{ money($inv->total) }}</td>
                            <td class="cell-money {{ $inv->remaining > 0 ? 'is-neg' : '' }}">{{ money($inv->remaining) }}</td>
                            <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                            <td class="col-actions">
                                <div class="btn-actions">
                                    <x-view-link :href="route('sales.show', $inv)" title="عرض الفاتورة" />
                                    <x-print-link :href="route('sales.print', $inv)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cell-empty text-center text-muted"><i class="fe fe-file-text"></i>لا توجد فواتير</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fe fe-credit-card"></i> المدفوعات</h4>
                <span class="count-badge">{{ $customer->payments->count() }} عملية</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>المبلغ</th><th>التاريخ</th><th>النوع</th></tr></thead>
                    <tbody>
                    @forelse($customer->payments as $p)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td class="cell-money is-pos">{{ money($p->amount) }}</td>
                            <td>{{ optional($p->transacted_at)->format('Y-m-d') }}</td>
                            <td><span class="badge badge-{{ $p->type === 'receipt' ? 'success' : 'warning' }}">{{ $p->type === 'receipt' ? 'تحصيل' : ($p->type === 'payment' ? 'صرف' : $p->type) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cell-empty text-center text-muted"><i class="fe fe-credit-card"></i>لا توجد مدفوعات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
