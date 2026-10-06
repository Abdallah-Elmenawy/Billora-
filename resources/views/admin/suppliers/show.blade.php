@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $supplier->name,
        'subtitle' => $supplier->code,
        'action' => '<a href="'.route('suppliers.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الموردين</a>
                     <a href="'.route('suppliers.edit', $supplier).'" class="btn btn-primary"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>
                     <button type="button" class="btn btn-light" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><i class="fe fe-briefcase"></i> بيانات المورد</h4></div>
            <div class="card-body">
                <div class="info-row"><span>الهاتف</span><strong dir="ltr">{{ $supplier->phone ?: '-' }}</strong></div>
                <div class="info-row"><span>البريد</span><strong dir="ltr">{{ $supplier->email ?: '-' }}</strong></div>
                <div class="info-row"><span>العنوان</span><strong>{{ $supplier->address ?: '-' }}</strong></div>
                <div class="info-row"><span>السجل التجاري</span><strong>{{ $supplier->tax_number ?: '-' }}</strong></div>
                <div class="info-row is-total"><span>الرصيد الحالي</span><strong class="{{ $supplier->current_balance > 0 ? 'text-danger' : 'text-success' }}">{{ money($supplier->current_balance) }}</strong></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h4 class="card-title"><i class="fe fe-upload"></i> صرف للمورد</h4></div>
            <div class="card-body">
                <form method="post" action="{{ route('suppliers.pay', $supplier) }}">@csrf
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
                            @foreach($supplier->invoices->whereIn('status', ['confirmed','partial']) as $inv)
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
                        <button class="btn btn-danger btn-block"><i class="fe fe-upload ml-1"></i> تسجيل الصرف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fe fe-file-text"></i> فواتير المشتريات</h4>
                <span class="count-badge">{{ $supplier->invoices->count() }} فاتورة</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>الرقم</th><th>التاريخ</th><th>الإجمالي</th><th>المتبقي</th><th>الحالة</th><th class="col-actions">الإجراءات</th></tr></thead>
                    <tbody>
                    @forelse($supplier->invoices as $inv)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td class="cell-strong"><a href="{{ route('purchases.show', $inv) }}">{{ $inv->number }}</a></td>
                            <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                            <td class="cell-money">{{ money($inv->total) }}</td>
                            <td class="cell-money {{ $inv->remaining > 0 ? 'is-neg' : '' }}">{{ money($inv->remaining) }}</td>
                            <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                            <td class="col-actions">
                                <div class="btn-actions">
                                    <x-view-link :href="route('purchases.show', $inv)" title="عرض الفاتورة" />
                                    <x-print-link :href="route('purchases.print', $inv)" />
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
    </div>
</div>
@endsection
