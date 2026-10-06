@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'القيود المحاسبية',
        'action' => can('accounting.create') ? '<a href="'.route('journals.create').'" class="btn btn-primary"><i class="fe fe-plus ml-1"></i> قيد جديد</a>' : '',
    ])
@endsection
@section('content')
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-10 form-group">
                <label>بحث</label>
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث برقم القيد أو الوصف...">
            </div>
            <div class="col-md-2 form-group">
                <button class="btn btn-primary btn-block"><i class="fe fe-search ml-1"></i> بحث</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-book"></i> القيود</h4>
        <span class="count-badge">{{ $journals->total() }} قيد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                    <th>المستخدم</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($journals as $j)
                <tr>
                    <td class="col-serial">{{ row_no($journals, $loop) }}</td>
                    <td class="cell-strong"><a href="{{ route('journals.show', $j) }}">{{ $j->number }}</a></td>
                    <td>{{ optional($j->journal_date)->format('Y-m-d') }}</td>
                    <td>{{ $j->description ?: '-' }}</td>
                    <td>{{ $j->creator->name ?? '-' }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('journals.show', $j)" title="عرض القيد" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-book"></i>لا توجد قيود</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($journals->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $journals->firstItem() }} - {{ $journals->lastItem() }} من {{ $journals->total() }}</span>
            {{ $journals->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
