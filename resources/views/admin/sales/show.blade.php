@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فاتورة '.$invoice->number,
        'subtitle' => 'فواتير المبيعات',
        'action' => '<a href="'.route('sales.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الفواتير</a>'
                     .(in_array($invoice->status, ['confirmed','partial']) && can('sales.update') ? '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#collectModal"><i class="fe fe-download ml-1"></i> تحصيل</button>' : '')
                     .'<a class="btn btn-primary" href="'.route('sales.print', $invoice).'" target="_blank"><i class="fe fe-printer ml-1"></i> طباعة</a>',
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-blue"><div class="card-body"><span>العميل</span><h3 class="tx-18">{{ $invoice->customer->name ?? '-' }}</h3><small class="text-muted">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</small></div></div></div>
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
                        @if(can('sales.update'))
                        <form class="d-inline" method="post" action="{{ route('sales.confirm', $invoice) }}">@csrf<button class="btn btn-success"><i class="fe fe-check ml-1"></i> تأكيد</button></form>
                        <x-edit-link :href="route('sales.edit', $invoice)" />
                        <form class="d-inline" method="post" action="{{ route('sales.cancel', $invoice) }}" data-confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" data-confirm-title="إلغاء الفاتورة" data-confirm-ok="نعم، إلغاء" data-confirm-icon="fe fe-x-circle">@csrf<button class="btn btn-icon-action btn-icon-delete" title="إلغاء"><i class="fe fe-x"></i><span class="sr-only">إلغاء</span></button></form>
                        @endif
                        <x-print-link :href="route('sales.print', $invoice)" />
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
                            <td class="cell-money is-pos">{{ money($p->amount) }}</td>
                            <td>{{ $p->treasury->name ?? '-' }}</td>
                            <td class="text-muted">{{ optional($p->transacted_at)->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cell-empty text-center text-muted"><i class="fe fe-credit-card"></i>لا توجد مدفوعات</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(in_array($invoice->status, ['confirmed','partial']) && can('sales.update'))
                <div class="card-footer text-center">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#collectModal"><i class="fe fe-download ml-1"></i> تحصيل من العميل</button>
                </div>
            @endif
        </div>
    </div>
</div>

@if(in_array($invoice->status, ['confirmed','partial']) && can('sales.update'))
<div class="modal fade" id="collectModal" tabindex="-1" role="dialog" aria-labelledby="collectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('sales.pay', $invoice) }}">
                @csrf
                <input type="hidden" name="_form" value="collect">
                <div class="modal-header">
                    <h5 class="modal-title" id="collectModalTitle">تحصيل فاتورة {{ $invoice->number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="pay-modal-summary">
                        <div><span>الإجمالي</span><strong>{{ money($invoice->total) }}</strong></div>
                        <div><span>المدفوع</span><strong class="text-success">{{ money($invoice->paid) }}</strong></div>
                        <div><span>المتبقي</span><strong class="text-danger">{{ money($invoice->remaining) }}</strong></div>
                    </div>
                    <div class="form-group">
                        <label for="collect_treasury">الخزينة</label>
                        <select id="collect_treasury" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                            @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected(old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                        </select>
                        @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-md-0">
                            <label for="collect_amount">المبلغ</label>
                            <input id="collect_amount" type="number" step="0.01" min="0.01" name="amount" max="{{ $invoice->remaining }}" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $invoice->remaining) }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label for="collect_date">التاريخ</label>
                            <input id="collect_date" type="date" name="transacted_at" class="form-control" value="{{ old('transacted_at', now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-success"><i class="fe fe-download ml-1"></i> تسجيل التحصيل</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
@if($errors->any() && old('_form') === 'collect')
<script>
$(function () { $('#collectModal').modal('show'); });
</script>
@endif
@endsection
