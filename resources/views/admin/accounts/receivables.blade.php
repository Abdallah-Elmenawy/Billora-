@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الحسابات المدينة'])
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
<table class="table"><thead><tr><th class="col-serial">#</th><th>العميل</th><th>الرصيد</th></tr></thead>
<tbody>
@foreach($customers as $c)
    <tr><td class="col-serial">{{ $loop->iteration }}</td><td><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td><td>{{ money($c->current_balance) }}</td></tr>
@endforeach
</tbody></table>
</div></div>
@endsection
