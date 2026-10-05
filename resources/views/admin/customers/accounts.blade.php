@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'حسابات العملاء'])
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="col-serial">#</th>
            <th>العميل</th>
            <th>المبيعات</th>
            <th>المدفوع</th>
            <th>الرصيد</th>
        </tr>
    </thead>
    <tbody>
    @foreach($customers as $c)
        <tr>
            <td class="col-serial">{{ row_no($customers, $loop) }}</td>
            <td><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
            <td>{{ money($c->sales_total) }}</td>
            <td>{{ money($c->paid_total) }}</td>
            <td>{{ money($c->current_balance) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $customers->links() }}
</div></div>
@endsection
