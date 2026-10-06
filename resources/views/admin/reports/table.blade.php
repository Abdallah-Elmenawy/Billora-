@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $title,
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>
                     <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fe fe-printer ml-1"></i> طباعة</button>',
    ])
@endsection
@section('content')
@if($from)
<div class="card report-filter">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-4 col-sm-6 form-group mb-md-0">
                <label>من تاريخ</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-md-4 col-sm-6 form-group mb-md-0">
                <label>إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-md-4 form-group mb-0">
                <button class="btn btn-primary"><i class="fe fe-filter ml-1"></i> تصفية</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">{{ $title }}</h4>
        <span class="badge badge-light report-count">{{ count($rows) }} سجل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th>@foreach($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr><td class="col-serial">{{ $loop->iteration }}</td>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headers) + 1 }}" class="text-center text-muted py-4">لا توجد بيانات لهذه الفترة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer report-total">
        <span>الإجمالي</span>
        <strong>{{ $total }}</strong>
    </div>
</div>
@endsection
