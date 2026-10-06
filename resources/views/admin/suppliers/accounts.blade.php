@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'حسابات الموردين',
        'action' => '<button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-briefcase"></i> حسابات الموردين</h4>
        <span class="count-badge">{{ $suppliers->total() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>المورد</th>
                    <th>المشتريات</th>
                    <th>المدفوع</th>
                    <th>الرصيد</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ row_no($suppliers, $loop) }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td class="cell-money">{{ money($s->purchases_total) }}</td>
                    <td class="cell-money is-pos">{{ money($s->paid_total) }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            <x-view-link :href="route('purchases.index', ['supplier_id' => $s->id])" title="فواتير المورد" icon="fe-file-text" class="btn-icon-print" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا يوجد موردون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $suppliers->firstItem() }} - {{ $suppliers->lastItem() }} من {{ $suppliers->total() }}</span>
            {{ $suppliers->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
