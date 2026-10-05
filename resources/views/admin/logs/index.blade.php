@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'سجل العمليات'])
@endsection
@section('content')
<div class="card"><div class="card-body table-responsive">
<table class="table">
    <thead><tr><th class="col-serial">#</th><th>الوصف</th><th>الوحدة</th><th>المستخدم</th><th>التاريخ</th></tr></thead>
    <tbody>
    @foreach($logs as $log)
        <tr>
            <td class="col-serial">{{ row_no($logs, $loop) }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->module }}</td>
            <td>{{ $log->user->name ?? '-' }}</td>
            <td>{{ $log->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{ $logs->links() }}
</div></div>
@endsection
