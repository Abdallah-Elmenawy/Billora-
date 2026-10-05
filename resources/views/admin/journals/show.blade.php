@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'قيد '.$journal->number])
@endsection
@section('content')
<div class="card"><div class="card-body">
<p>{{ $journal->description }} — {{ optional($journal->journal_date)->format('Y-m-d') }}</p>
<table class="table"><thead><tr><th class="col-serial">#</th><th>الحساب</th><th>مدين</th><th>دائن</th></tr></thead>
<tbody>
@foreach($journal->lines as $line)
    <tr><td class="col-serial">{{ $loop->iteration }}</td><td>{{ $line->account->code }} — {{ $line->account->name }}</td><td>{{ money($line->debit) }}</td><td>{{ money($line->credit) }}</td></tr>
@endforeach
</tbody></table>
</div></div>
@endsection
