@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => $title])
@endsection
@section('content')
<div class="card"><div class="card-body">
    @if($from)
    <form class="form-inline mb-3" method="get">
        <input type="date" name="from" value="{{ $from }}" class="form-control ml-2">
        <input type="date" name="to" value="{{ $to }}" class="form-control ml-2">
        <button class="btn btn-secondary">تصفية</button>
    </form>
    @endif
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead><tr><th class="col-serial">#</th>@foreach($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($rows as $row)
                <tr><td class="col-serial">{{ $loop->iteration }}</td>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h5>الإجمالي: {{ $total }}</h5>
</div></div>
@endsection
