@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'التقارير'])
@endsection
@section('content')
<div class="row">
    @foreach([
        ['تقرير المبيعات', route('reports.sales')],
        ['تقرير المشتريات', route('reports.purchases')],
        ['تقرير المخزون', route('reports.inventory')],
        ['تقرير العملاء', route('reports.customers')],
        ['تقرير الموردين', route('reports.suppliers')],
        ['المصروفات والإيرادات', route('reports.expenses')],
        ['الأرباح والخسائر', route('reports.profit')],
        ['التقارير المالية', route('reports.financial')],
        ['دفتر الأستاذ', route('reports.ledger')],
    ] as $r)
        <div class="col-md-4"><a class="card" href="{{ $r[1] }}"><div class="card-body text-center"><h5>{{ $r[0] }}</h5></div></a></div>
    @endforeach
</div>
@endsection
