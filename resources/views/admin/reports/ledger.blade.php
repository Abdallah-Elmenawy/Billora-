@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'دفتر الأستاذ'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="get" class="form-inline mb-3">
    <select name="account_id" class="form-control ml-2">
        @foreach($accounts as $a)<option value="{{ $a->id }}" @selected(optional($account)->id==$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
    </select>
    <button class="btn btn-secondary">عرض</button>
</form>
@if($account)
<h5>{{ $account->code }} — {{ $account->name }}</h5>
<table class="table"><thead><tr><th class="col-serial">#</th><th>القيد</th><th>مدين</th><th>دائن</th></tr></thead>
<tbody>
@foreach($lines as $line)
    <tr>
        <td class="col-serial">{{ row_no($lines, $loop) }}</td>
        <td>{{ $line->journal->number ?? '-' }} — {{ $line->journal->description ?? '' }}</td>
        <td>{{ money($line->debit) }}</td>
        <td>{{ money($line->credit) }}</td>
    </tr>
@endforeach
</tbody></table>
@if(method_exists($lines, 'links')) {{ $lines->links() }} @endif
@endif
</div></div>
@endsection
