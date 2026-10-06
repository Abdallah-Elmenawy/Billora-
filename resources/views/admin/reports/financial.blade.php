@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'التقارير المالية',
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>
                     <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
@php
    $typeLabels = [
        'asset' => ['الأصول', 'fe-layers', 'blue'],
        'liability' => ['الالتزامات', 'fe-credit-card', 'orange'],
        'equity' => ['حقوق الملكية', 'fe-award', 'purple'],
        'revenue' => ['الإيرادات', 'fe-trending-up', 'green'],
        'expense' => ['المصروفات', 'fe-trending-down', 'pink'],
    ];
    $order = array_keys($typeLabels);
    $accounts = $accounts->sortBy(fn ($rows, $type) => array_search($type, $order));
@endphp

<div class="row">
    @foreach($accounts as $type => $rows)
        @php [$label, $icon, $color] = $typeLabels[$type] ?? [$type, 'fe-file-text', 'blue']; @endphp
        <div class="col-md-4 col-sm-6">
            <div class="card report-stat stat-{{ $color }}">
                <div class="card-body">
                    <span><i class="fe {{ $icon }} ml-1"></i> {{ $label }}</span>
                    <h3>{{ money($rows->sum('current_balance')) }}</h3>
                    <small class="text-muted">{{ $rows->count() }} حساب</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

@foreach($accounts as $type => $rows)
    @php [$label, $icon] = $typeLabels[$type] ?? [$type, 'fe-file-text']; @endphp
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title mb-0"><i class="fe {{ $icon }} ml-1"></i> {{ $label }}</h4>
            <span class="badge badge-light report-count">{{ $rows->count() }} حساب</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead><tr><th class="col-serial">#</th><th>الكود</th><th>الحساب</th><th class="text-left">الرصيد</th></tr></thead>
                <tbody>
                @foreach($rows as $a)
                    <tr>
                        <td class="col-serial">{{ $loop->iteration }}</td>
                        <td>{{ $a->code }}</td>
                        <td>{{ $a->name }}</td>
                        <td class="text-left">{{ money($a->current_balance) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer report-total">
            <span>إجمالي {{ $label }}</span>
            <strong>{{ money($rows->sum('current_balance')) }}</strong>
        </div>
    </div>
@endforeach
@endsection
