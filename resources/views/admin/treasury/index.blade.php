@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الخزينة',
        'subtitle' => 'إدارة الخزائن والتحصيلات والمصروفات والتحويلات',
        'action' => can('treasury.create') ? '
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTreasuryModal"><i class="fe fe-plus ml-1"></i> خزينة جديدة</button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#receiptModal"><i class="fe fe-download ml-1"></i> تحصيل من عميل</button>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#paymentModal"><i class="fe fe-upload ml-1"></i> صرف لمورد</button>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#transferModal"><i class="fe fe-repeat ml-1"></i> تحويل بين الخزائن</button>
        ' : '',
    ])
@endsection
@section('content')
@php
    $openModal = old('_form');
    $methodLabels = ['cash' => 'نقدي', 'bank' => 'بنك', 'transfer' => 'تحويل'];
@endphp

<div class="row">
    @forelse($treasuriesAll as $t)
        <div class="col-md-4 col-sm-6">
            <div class="card treasury-card">
                <div class="card-body d-flex align-items-center">
                    <div class="treasury-icon {{ $t->type === 'bank' ? 'is-bank' : 'is-cash' }}">
                        <i class="fe {{ $t->type === 'bank' ? 'fe-credit-card' : 'fe-box' }}"></i>
                    </div>
                    <div class="mr-3">
                        <h5 class="mb-1">{{ $t->name }}</h5>
                        <p class="text-muted mb-1">{{ $t->type === 'bank' ? 'بنك' : 'صندوق' }}</p>
                        <h3 class="mb-0">{{ money($t->current_balance) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body text-center text-muted py-4">لا توجد خزائن بعد — ابدأ بإضافة خزينة جديدة.</div></div>
        </div>
    @endforelse
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">آخر التحصيلات</h4></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>العميل</th><th>الخزينة</th><th>المبلغ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($receipts as $r)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td>{{ $r->party->name ?? '-' }}</td>
                            <td>{{ $r->treasury->name ?? '-' }}</td>
                            <td class="text-success font-weight-bold">{{ money($r->amount) }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($r->transacted_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا توجد تحصيلات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">آخر الصرف</h4></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>المورد</th><th>الخزينة</th><th>المبلغ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($payments as $r)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td>{{ $r->party->name ?? '-' }}</td>
                            <td>{{ $r->treasury->name ?? '-' }}</td>
                            <td class="text-danger font-weight-bold">{{ money($r->amount) }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($r->transacted_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا توجد عمليات صرف</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">التحويلات</h4></div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>من</th><th>إلى</th><th>المبلغ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($transfers as $tr)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td>{{ $tr->fromTreasury->name ?? '-' }}</td>
                            <td>{{ $tr->toTreasury->name ?? '-' }}</td>
                            <td class="font-weight-bold">{{ money($tr->amount) }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Carbon::parse($tr->transferred_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">لا توجد تحويلات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- خزينة جديدة --}}
<div class="modal fade" id="addTreasuryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('treasury.store') }}">
                @csrf
                <input type="hidden" name="_form" value="treasury">
                <div class="modal-header">
                    <h5 class="modal-title">خزينة جديدة</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="form-group">
                        <label for="treasury_name">الاسم</label>
                        <input id="treasury_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $openModal === 'treasury' ? old('name') : '' }}" placeholder="مثال: الخزينة الرئيسية" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="treasury_type">النوع</label>
                            <select id="treasury_type" name="type" class="form-control">
                                <option value="cash" @selected(old('type') === 'cash')>صندوق</option>
                                <option value="bank" @selected(old('type') === 'bank')>بنك</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="treasury_opening">رصيد أول المدة</label>
                            <input id="treasury_opening" name="opening_balance" type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" value="{{ $openModal === 'treasury' ? old('opening_balance', 0) : 0 }}">
                            @error('opening_balance')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">إضافة الخزينة</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- تحصيل من عميل --}}
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('treasury.receipt') }}">
                @csrf
                <input type="hidden" name="_form" value="receipt">
                <div class="modal-header">
                    <h5 class="modal-title">تحصيل من عميل</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>الخزينة</label>
                            <select name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                                @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected($openModal === 'receipt' && old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                            </select>
                            @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>العميل</label>
                            <select name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                @foreach($customers as $c)<option value="{{ $c->id }}" @selected($openModal === 'receipt' && old('customer_id') == $c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 form-group">
                            <label>الفاتورة</label>
                            <select name="invoice_id" class="form-control">
                                <option value="">بدون فاتورة</option>
                                @foreach($saleInvoices as $inv)<option value="{{ $inv->id }}" @selected($openModal === 'receipt' && old('invoice_id') == $inv->id)>{{ $inv->number }} — {{ money($inv->total) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>المبلغ</label>
                            <input name="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ $openModal === 'receipt' ? old('amount') : '' }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>التاريخ</label>
                            <input name="transacted_at" type="date" class="form-control" value="{{ $openModal === 'receipt' ? old('transacted_at', now()->toDateString()) : now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label>طريقة الدفع</label>
                            <select name="method" class="form-control">
                                @foreach($methodLabels as $value => $label)<option value="{{ $value }}" @selected($openModal === 'receipt' && old('method') === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-success">تسجيل التحصيل</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- صرف لمورد --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('treasury.payment') }}">
                @csrf
                <input type="hidden" name="_form" value="payment">
                <div class="modal-header">
                    <h5 class="modal-title">صرف لمورد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>الخزينة</label>
                            <select name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                                @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected($openModal === 'payment' && old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                            </select>
                            @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>المورد</label>
                            <select name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                @foreach($suppliers as $s)<option value="{{ $s->id }}" @selected($openModal === 'payment' && old('supplier_id') == $s->id)>{{ $s->name }}</option>@endforeach
                            </select>
                            @error('supplier_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 form-group">
                            <label>الفاتورة</label>
                            <select name="invoice_id" class="form-control">
                                <option value="">بدون فاتورة</option>
                                @foreach($purchaseInvoices as $inv)<option value="{{ $inv->id }}" @selected($openModal === 'payment' && old('invoice_id') == $inv->id)>{{ $inv->number }} — {{ money($inv->total) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>المبلغ</label>
                            <input name="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ $openModal === 'payment' ? old('amount') : '' }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>التاريخ</label>
                            <input name="transacted_at" type="date" class="form-control" value="{{ $openModal === 'payment' ? old('transacted_at', now()->toDateString()) : now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label>طريقة الدفع</label>
                            <select name="method" class="form-control">
                                @foreach($methodLabels as $value => $label)<option value="{{ $value }}" @selected($openModal === 'payment' && old('method') === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-danger">تسجيل الصرف</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- تحويل بين الخزائن --}}
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('treasury.transfer') }}">
                @csrf
                <input type="hidden" name="_form" value="transfer">
                <div class="modal-header">
                    <h5 class="modal-title">تحويل بين الخزائن</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>من خزينة</label>
                            <select name="from_treasury_id" class="form-control @error('from_treasury_id') is-invalid @enderror" required>
                                @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected($openModal === 'transfer' && old('from_treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                            </select>
                            @error('from_treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>إلى خزينة</label>
                            <select name="to_treasury_id" class="form-control @error('to_treasury_id') is-invalid @enderror" required>
                                @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected($openModal === 'transfer' ? old('to_treasury_id') == $t->id : $loop->iteration === 2)>{{ $t->name }}</option>@endforeach
                            </select>
                            @error('to_treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label>المبلغ</label>
                            <input name="amount" type="number" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ $openModal === 'transfer' ? old('amount') : '' }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label>التاريخ</label>
                            <input name="transferred_at" type="date" class="form-control" value="{{ $openModal === 'transfer' ? old('transferred_at', now()->toDateString()) : now()->toDateString() }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-warning">تنفيذ التحويل</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
@if($errors->any() && in_array($openModal, ['treasury', 'receipt', 'payment', 'transfer'], true))
<script>
$(function () {
    var map = { treasury: '#addTreasuryModal', receipt: '#receiptModal', payment: '#paymentModal', transfer: '#transferModal' };
    $(map[@json($openModal)]).modal('show');
});
</script>
@endif
@endsection
