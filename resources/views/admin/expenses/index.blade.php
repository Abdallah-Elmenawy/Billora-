@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المصروفات والإيرادات',
        'action' => can('expenses.create') ? '<button type="button" class="btn btn-light ml-2" data-toggle="modal" data-target="#addCategoryModal"><i class="fe fe-tag ml-1"></i> إضافة تصنيف</button><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addEntryModal"><i class="fe fe-plus ml-1"></i> إضافة عملية</button>' : '',
    ])
@endsection
@section('content')
@php
    $openAddModal = old('_form') === 'entry';
    $openCategoryModal = old('_form') === 'category';
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-dollar-sign"></i> المصروفات والإيرادات</h4>
        <span class="count-badge">{{ $entries->total() }} عملية</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>النوع</th>
                    <th>التصنيف</th>
                    <th>الخزينة</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                    <th>تاريخ التسجيل</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($entries as $e)
                <tr>
                    <td class="col-serial">{{ row_no($entries, $loop) }}</td>
                    <td class="cell-strong">{{ $e->number }}</td>
                    <td><span class="badge badge-{{ $e->type === 'expense' ? 'danger' : 'success' }}">{{ $e->type === 'expense' ? 'مصروف' : 'إيراد' }}</span></td>
                    <td>{{ $e->category->name ?? '-' }}</td>
                    <td>{{ $e->treasury->name ?? '-' }}</td>
                    <td class="cell-money {{ $e->type === 'expense' ? 'is-neg' : 'is-pos' }}">{{ money($e->amount) }}</td>
                    <td>{{ optional($e->entry_date)->format('Y-m-d') }}</td>
                    <td class="text-muted">{{ optional($e->created_at)->format('H:i Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                                @if(can('expenses.update'))<x-edit-link :href="route('expenses.edit', $e)" />@endif
                                @if(can('expenses.delete'))<x-delete-form :action="route('expenses.destroy', $e)" message="هل أنت متأكد من حذف هذه العملية؟ سيتم التراجع عن تأثيرها على الخزينة." title="حذف العملية" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="cell-empty text-center text-muted"><i class="fe fe-dollar-sign"></i>لا توجد عمليات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($entries->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $entries->firstItem() }} - {{ $entries->lastItem() }} من {{ $entries->total() }}</span>
            {{ $entries->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addEntryModal" tabindex="-1" role="dialog" aria-labelledby="addEntryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('expenses.store') }}">
                @csrf
                <input type="hidden" name="_form" value="entry">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEntryModalTitle">إضافة عملية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_type">النوع</label>
                            <select id="add_type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="expense" @selected(old('type', 'expense') === 'expense')>مصروف</option>
                                <option value="revenue" @selected(old('type') === 'revenue')>إيراد</option>
                            </select>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_category">التصنيف</label>
                            <select id="add_category" name="money_category_id" class="form-control @error('money_category_id') is-invalid @enderror" required>
                                <option value="">اختر التصنيف</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" data-type="{{ $c->type }}" @selected(old('money_category_id') == $c->id)>{{ $c->name }} ({{ $c->type === 'expense' ? 'مصروف' : 'إيراد' }})</option>
                                @endforeach
                            </select>
                            @error('money_category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_treasury">الخزينة</label>
                            <select id="add_treasury" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                                @foreach($treasuries as $t)
                                    <option value="{{ $t->id }}" @selected(old('treasury_id') == $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_amount">المبلغ</label>
                            <input id="add_amount" name="amount" type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_entry_date">التاريخ</label>
                            <input id="add_entry_date" name="entry_date" type="date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', now()->toDateString()) }}" required>
                            @error('entry_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_description">البيان</label>
                            <input id="add_description" name="description" class="form-control" value="{{ old('description') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('expenses.categories.store') }}">
                @csrf
                <input type="hidden" name="_form" value="category">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalTitle">إضافة تصنيف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="form-group">
                        <label for="cat_name">اسم التصنيف</label>
                        <input id="cat_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="cat_type">النوع</label>
                        <select id="cat_type" name="type" class="form-control">
                            <option value="expense" @selected(old('type') === 'expense')>مصروف</option>
                            <option value="revenue" @selected(old('type') === 'revenue')>إيراد</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
@if($openAddModal)
<script>
$(function () { $('#addEntryModal').modal('show'); });
</script>
@endif
@if($openCategoryModal)
<script>
$(function () { $('#addCategoryModal').modal('show'); });
</script>
@endif
@endsection
