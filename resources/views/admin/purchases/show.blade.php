@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فاتورة '.$invoice->number,
        'subtitle' => 'فواتير المشتريات',
        'action' => '<a href="'.route('purchases.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الفواتير</a>'
                     .(in_array($invoice->status, ['confirmed','partial']) ? '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#payModal"><i class="fe fe-upload ml-1"></i> صرف للمورد</button>' : '')
                     .'<a class="btn btn-primary" href="'.route('purchases.print', $invoice).'" target="_blank"><i class="fe fe-printer ml-1"></i> طباعة</a>',
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-blue"><div class="card-body"><span>المورد</span><h3 class="tx-18">{{ $invoice->supplier->name ?? '-' }}</h3><small class="text-muted">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</small></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-purple"><div class="card-body"><span>الإجمالي</span><h3>{{ money($invoice->total) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-green"><div class="card-body"><span>المدفوع</span><h3>{{ money($invoice->paid) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat {{ $invoice->remaining > 0 ? 'stat-red' : 'stat-teal' }}"><div class="card-body"><span>المتبقي</span><h3>{{ money($invoice->remaining) }}</h3></div></div></div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fe fe-list"></i> بنود الفاتورة</h4>
                <span class="badge badge-{{ invoice_status_badge($invoice->status) }}">{{ invoice_status_label($invoice->status) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
                    <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td class="cell-strong">{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td class="cell-money">{{ money($item->unit_price) }}</td>
                            <td class="cell-money">{{ money($item->total) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer table-card-footer">
                <div class="report-total flex-grow-1"><span>الإجمالي</span><strong>{{ money($invoice->total) }}</strong></div>
                @if($invoice->status==='draft')
                    <div class="btn-actions">
                        <form class="d-inline" method="post" action="{{ route('purchases.confirm', $invoice) }}">@csrf<button class="btn btn-success"><i class="fe fe-check ml-1"></i> تأكيد</button></form>
                        <x-edit-link :href="route('purchases.edit', $invoice)" />
                        <x-print-link :href="route('purchases.print', $invoice)" />
                        <form class="d-inline" method="post" action="{{ route('purchases.cancel', $invoice) }}" data-confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" data-confirm-title="إلغاء الفاتورة" data-confirm-ok="نعم، إلغاء" data-confirm-icon="fe fe-x-circle">@csrf<button class="btn btn-icon-action btn-icon-delete" title="إلغاء"><i class="fe fe-x"></i><span class="sr-only">إلغاء</span></button></form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fe fe-credit-card"></i> المدفوعات</h4>
                <span class="count-badge">{{ $invoice->payments->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th class="col-serial">#</th><th>المبلغ</th><th>الخزينة</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($invoice->payments as $p)
                        <tr>
                            <td class="col-serial">{{ $loop->iteration }}</td>
                            <td class="cell-money is-neg">{{ money($p->amount) }}</td>
                            <td>{{ $p->treasury->name ?? '-' }}</td>
                            <td class="text-muted">{{ optional($p->transacted_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cell-empty text-center text-muted"><i class="fe fe-credit-card"></i>لا توجد مدفوعات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(in_array($invoice->status, ['confirmed','partial']))
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#payModal"><i class="fe fe-upload ml-1"></i> صرف للمورد</button>
                </div>
            @endif
        </div>
    </div>
</div>

@if(in_array($invoice->status, ['confirmed','partial']))
<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('purchases.pay', $invoice) }}">
                @csrf
                <input type="hidden" name="_form" value="pay">
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalTitle">صرف فاتورة {{ $invoice->number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="pay-modal-summary">
                        <div><span>الإجمالي</span><strong>{{ money($invoice->total) }}</strong></div>
                        <div><span>المدفوع</span><strong class="text-success">{{ money($invoice->paid) }}</strong></div>
                        <div><span>المتبقي</span><strong class="text-danger">{{ money($invoice->remaining) }}</strong></div>
                    </div>
                    <div class="form-group">
                        <label for="pay_treasury">الخزينة</label>
                        <select id="pay_treasury" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                            @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected(old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                        </select>
                        @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-md-0">
                            <label for="pay_amount">المبلغ</label>
                            <input id="pay_amount" type="number" step="0.01" min="0.01" name="amount" max="{{ $invoice->remaining }}" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $invoice->remaining) }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label for="pay_date">التاريخ</label>
                            <input id="pay_date" type="date" name="transacted_at" class="form-control" value="{{ old('transacted_at', now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-danger"><i class="fe fe-upload ml-1"></i> تسجيل الصرف</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
@if($errors->any() && old('_form') === 'pay')
<script>
$(function () { $('#payModal').modal('show'); });
</script>
@endif
@endsection
