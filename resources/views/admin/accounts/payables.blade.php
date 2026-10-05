@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'الحسابات الدائنة'])
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
<table class="table"><thead><tr><th class="col-serial">#</th><th>المورد</th><th>الرصيد</th></tr></thead>
<tbody>
@foreach($suppliers as $s)
    <tr><td class="col-serial">{{ $loop->iteration }}</td><td><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td><td>{{ money($s->current_balance) }}</td></tr>
@endforeach
</tbody></table>
</div></div>
@endsection
