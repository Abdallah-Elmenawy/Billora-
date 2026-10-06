@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الحسابات المدينة',
        'subtitle' => 'أرصدة العملاء المستحقة',
        'action' => '<button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> العملاء</h4>
        <span class="count-badge">{{ $customers->count() }} عميل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>الكود</th><th>العميل</th><th>الموبايل</th><th>الرصيد</th><th class="col-actions">الإجراءات</th></tr></thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td class="text-muted">{{ $c->code }}</td>
                    <td class="cell-strong"><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td dir="ltr">{{ $c->phone ?: '-' }}</td>
                    <td class="cell-money {{ $c->current_balance > 0 ? 'is-neg' : '' }}">{{ money($c->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('customers.show', $c)" title="كشف الحساب" />
                            <x-edit-link :href="route('customers.edit', $c)" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا توجد حسابات مدينة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer report-total">
        <span>إجمالي الأرصدة المدينة</span>
        <strong>{{ money($customers->sum('current_balance')) }}</strong>
    </div>
</div>
@endsection
