@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'تصنيفات المنتجات',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal"><i class="fe fe-plus ml-1"></i> إضافة تصنيف</button>',
    ])
@endsection
@section('content')
@php $openAddModal = $errors->any(); @endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-tag"></i> التصنيفات</h4>
        <span class="count-badge">{{ $categories->total() }} تصنيف</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الاسم</th>
                    <th>عدد المنتجات</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $c)
                <tr>
                    <td class="col-serial">{{ row_no($categories, $loop) }}</td>
                    <td>
                        <form method="post" action="{{ route('categories.update', $c) }}" id="category-form-{{ $c->id }}" class="d-inline-flex align-items-center justify-content-center">
                            @csrf @method('PUT')
                            <input name="name" class="form-control account-name-input" value="{{ $c->name }}" required>
                        </form>
                    </td>
                    <td><span class="badge badge-light">{{ $c->products_count }} منتج</span></td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <button type="submit" form="category-form-{{ $c->id }}" class="btn btn-icon-action btn-icon-edit" title="حفظ التعديل">
                                <i class="fe fe-check"></i><span class="sr-only">حفظ التعديل</span>
                            </button>
                            <x-delete-form :action="route('categories.destroy', $c)" message="هل أنت متأكد من حذف هذا التصنيف؟" title="حذف التصنيف" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="cell-empty text-center text-muted"><i class="fe fe-tag"></i>لا توجد تصنيفات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $categories->firstItem() }} - {{ $categories->lastItem() }} من {{ $categories->total() }}</span>
            {{ $categories->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalTitle">إضافة تصنيف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="form-group mb-0">
                        <label for="category_name">اسم التصنيف</label>
                        <input id="category_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="مثال: مشروبات">
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">إضافة التصنيف</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
@if($openAddModal)
<script>
$(function () { $('#addCategoryModal').modal('show'); });
</script>
@endif
@endsection
