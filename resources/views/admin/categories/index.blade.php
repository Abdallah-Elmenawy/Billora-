@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تصنيفات المنتجات'])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-header">إضافة تصنيف</div><div class="card-body">
            <form method="post" action="{{ route('categories.store') }}">@csrf
                <input name="name" class="form-control mb-2" required placeholder="اسم التصنيف">
                <div class="form-actions">
                    <button class="btn btn-success">إضافة تصنيف</button>
                </div>
            </form>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card"><div class="card-body table-responsive">
            <table class="table">
                <thead><tr><th class="col-serial">#</th><th>الاسم</th><th class="col-actions">الإجراءات</th></tr></thead>
                <tbody>
                @foreach($categories as $c)
                    <tr>
                        <td class="col-serial">{{ row_no($categories, $loop) }}</td>
                        <td>
                            <form method="post" action="{{ route('categories.update', $c) }}" class="d-flex align-items-center">@csrf @method('PUT')
                                <input name="name" class="form-control ml-2" value="{{ $c->name }}">
                                <button type="submit" class="btn btn-icon-action btn-icon-edit" title="تحديث">
                                    <i class="fe fe-check"></i>
                                    <span class="sr-only">تحديث</span>
                                </button>
                            </form>
                        </td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <x-delete-form :action="route('categories.destroy', $c)" message="هل أنت متأكد من حذف هذا التصنيف؟" title="حذف التصنيف" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $categories->links() }}
        </div></div>
    </div>
</div>
@endsection
