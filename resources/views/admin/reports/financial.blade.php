@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'التقارير المالية'])
@endsection
@section('content')
@foreach($accounts as $type => $rows)
<div class="card"><div class="card-header">{{ $type }}</div><div class="card-body">
<table class="table"><thead><tr><th class="col-serial">#</th><th>الكود</th><th>الحساب</th><th>الرصيد</th></tr></thead>
<tbody>
@foreach($rows as $a)
    <tr><td class="col-serial">{{ $loop->iteration }}</td><td>{{ $a->code }}</td><td>{{ $a->name }}</td><td>{{ money($a->current_balance) }}</td></tr>
@endforeach
</tbody></table>
</div></div>
@endforeach
@endsection
