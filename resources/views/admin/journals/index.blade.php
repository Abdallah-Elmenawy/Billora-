@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'القيود المحاسبية', 'action' => '<a href="'.route('journals.create').'" class="btn btn-primary">قيد جديد</a>'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="get" class="form-inline mb-3"><input name="q" value="{{ request('q') }}" class="form-control ml-2"><button class="btn btn-secondary">بحث</button></form>
<table class="table"><thead><tr><th class="col-serial">#</th><th>الرقم</th><th>التاريخ</th><th>الوصف</th><th>المستخدم</th></tr></thead>
<tbody>
@forelse($journals as $j)
    <tr>
        <td class="col-serial">{{ row_no($journals, $loop) }}</td>
        <td><a href="{{ route('journals.show', $j) }}">{{ $j->number }}</a></td>
        <td>{{ optional($j->journal_date)->format('Y-m-d') }}</td>
        <td>{{ $j->description }}</td>
        <td>{{ $j->creator->name ?? '-' }}</td>
    </tr>
@empty
    <tr><td colspan="5" class="text-center">لا توجد قيود</td></tr>
@endforelse
</tbody></table>
{{ $journals->links() }}
</div></div>
@endsection
