@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الأرباح والخسائر',
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>
                     <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
@php $net = $sales + $revenues - $purchases - $expenses; @endphp
<div class="card report-filter">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-4 col-sm-6 form-group mb-md-0">
                <label>من تاريخ</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-md-4 col-sm-6 form-group mb-md-0">
                <label>إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-md-4 form-group mb-0">
                <button class="btn btn-primary"><i class="fe fe-filter ml-1"></i> تصفية</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-green"><div class="card-body"><span>المبيعات</span><h3>{{ money($sales) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-teal"><div class="card-body"><span>إيرادات أخرى</span><h3>{{ money($revenues) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-orange"><div class="card-body"><span>المشتريات</span><h3>{{ money($purchases) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-red"><div class="card-body"><span>المصروفات</span><h3>{{ money($expenses) }}</h3></div></div></div>
</div>

<div class="card">
    <div class="card-header"><h4 class="card-title mb-0">قائمة الدخل</h4></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead><tr><th class="col-serial">#</th><th>البند</th><th class="text-left">المبلغ</th></tr></thead>
            <tbody>
            <tr><td class="col-serial">1</td><td>المبيعات</td><td class="text-left text-success">{{ money($sales) }}</td></tr>
            <tr><td class="col-serial">2</td><td>إيرادات أخرى</td><td class="text-left text-success">{{ money($revenues) }}</td></tr>
            <tr><td class="col-serial">3</td><td>المشتريات</td><td class="text-left text-danger">({{ money($purchases) }})</td></tr>
            <tr><td class="col-serial">4</td><td>المصروفات</td><td class="text-left text-danger">({{ money($expenses) }})</td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer report-total {{ $net >= 0 ? 'is-profit' : 'is-loss' }}">
        <span>{{ $net >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</span>
        <strong>{{ money(abs($net)) }}</strong>
    </div>
</div>
@endsection
