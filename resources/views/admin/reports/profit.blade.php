@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الأرباح والخسائر'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form class="form-inline mb-3" method="get">
    <input type="date" name="from" value="{{ $from }}" class="form-control ml-2">
    <input type="date" name="to" value="{{ $to }}" class="form-control ml-2">
    <button class="btn btn-secondary">تصفية</button>
</form>
<table class="table">
    <thead><tr><th class="col-serial">#</th><th>البند</th><th>المبلغ</th></tr></thead>
    <tbody>
    <tr><td class="col-serial">1</td><td>المبيعات</td><td>{{ money($sales) }}</td></tr>
    <tr><td class="col-serial">2</td><td>إيرادات أخرى</td><td>{{ money($revenues) }}</td></tr>
    <tr><td class="col-serial">3</td><td>المشتريات</td><td>{{ money($purchases) }}</td></tr>
    <tr><td class="col-serial">4</td><td>المصروفات</td><td>{{ money($expenses) }}</td></tr>
    <tr><td></td><th>صافي الربح</th><th>{{ money($sales + $revenues - $purchases - $expenses) }}</th></tr>
    </tbody>
</table>
</div></div>
@endsection
