@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الأدوار والصلاحيات',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRoleModal"><i class="fe fe-plus ml-1"></i> إضافة دور</button>',
    ])
@endsection
@section('content')
@php $openAddModal = $errors->any(); @endphp

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>اسم الدور</th>
                        <th>الوصف</th>
                        <th>الصلاحيات</th>
                        <th>المستخدمون</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="col-serial">{{ row_no($roles, $loop) }}</td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->description ?: '-' }}</td>
                        <td>{{ $role->permissions->count() }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <x-edit-link :href="route('roles.edit', $role)" />
                                @if($role->slug !== 'admin')
                                    <x-delete-form :action="route('roles.destroy', $role)" message="هل أنت متأكد من حذف هذا الدور؟" title="حذف الدور" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">لا توجد أدوار بعد. أضف دورًا ثم أنشئ مستخدمًا.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleModalTitle">إضافة دور</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="role_name">اسم الدور</label>
                            <input id="role_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="مثال: محاسب">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="role_description">الوصف</label>
                            <input id="role_description" name="description" class="form-control" value="{{ old('description') }}" placeholder="وصف مختصر للدور">
                        </div>
                    </div>
                    <h5 class="mb-3">الصلاحيات</h5>
                    <div class="row">
                        @foreach($permissions as $module => $items)
                            <div class="col-md-6 mb-3">
                                <div class="perm-card">
                                    <h6>{{ $module }}</h6>
                                    @foreach($items as $p)
                                        <label class="perm-item">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(collect(old('permissions', []))->contains($p->id))>
                                            <span>{{ $p->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
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
$(function () { $('#addRoleModal').modal('show'); });
</script>
@endif
@endsection
