@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'حسابات الموردين'])
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered">
    <thead><tr><th class="col-serial">#</th><th>المورد</th><th>المشتريات</th><th>المدفوع</th><th>الرصيد</th></tr></thead>
    <tbody>
    @foreach($suppliers as $s)
        <tr>
            <td class="col-serial">{{ row_no($suppliers, $loop) }}</td>
            <td><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
            <td>{{ money($s->purchases_total) }}</td>
            <td>{{ money($s->paid_total) }}</td>
            <td>{{ money($s->current_balance) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $suppliers->links() }}
</div></div>
@endsection
