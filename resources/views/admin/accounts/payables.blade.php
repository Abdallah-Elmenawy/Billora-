@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الحسابات الدائنة',
        'subtitle' => 'أرصدة الموردين المستحقة',
        'action' => '<button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-briefcase"></i> الموردون</h4>
        <span class="count-badge">{{ $suppliers->count() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>الكود</th><th>المورد</th><th>الموبايل</th><th>الرصيد</th><th class="col-actions">الإجراءات</th></tr></thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td class="text-muted">{{ $s->code }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td dir="ltr">{{ $s->phone ?: '-' }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            @if(can('suppliers.update'))<x-edit-link :href="route('suppliers.edit', $s)" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا توجد حسابات دائنة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer report-total">
        <span>إجمالي الأرصدة الدائنة</span>
        <strong>{{ money($suppliers->sum('current_balance')) }}</strong>
    </div>
</div>
@endsection
