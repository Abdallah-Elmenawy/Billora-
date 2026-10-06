@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المستخدمون',
        'action' => can('users.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal"><i class="fe fe-user-plus ml-1"></i> إضافة مستخدم</button>' : '',
    ])
@endsection
@section('content')
@php
    $statusLabels = ['active' => 'نشط', 'disabled' => 'معطّل', 'suspended' => 'موقوف'];
    $openAddModal = $errors->any();
@endphp

@php $statusBadges = ['active' => 'success', 'disabled' => 'secondary', 'suspended' => 'danger']; @endphp
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> المستخدمون</h4>
        <span class="count-badge">{{ $users->total() }} مستخدم</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="col-serial">{{ row_no($users, $loop) }}</td>
                    <td class="cell-strong">{{ $u->name }}</td>
                    <td dir="ltr">{{ $u->email }}</td>
                    <td>{{ $u->role->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $statusBadges[$u->status] ?? 'light' }}">{{ $statusLabels[$u->status] ?? $u->status }}</span></td>
                    <td class="col-actions">
                        <div class="btn-actions">
                                @if(can('users.update'))<x-edit-link :href="route('users.edit', $u)" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا يوجد مستخدمون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $users->firstItem() }} - {{ $users->lastItem() }} من {{ $users->total() }}</span>
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalTitle">إضافة مستخدم</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_name">الاسم</label>
                            <input id="add_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_username">اسم المستخدم</label>
                            <input id="add_username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}">
                            @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_email">البريد الالكتروني</label>
                            <input id="add_email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_phone">الهاتف</label>
                            <input id="add_phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_password">كلمة المرور</label>
                            <input id="add_password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_role">الدور</label>
                            @if($roles->isEmpty())
                                <div class="alert alert-warning mb-0">لا يوجد أدوار بعد. <a href="{{ route('roles.index') }}">أضف دورًا أولاً</a>.</div>
                            @else
                                <select id="add_role" name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                    <option value="">اختر الدور</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" @selected(old('role_id') == $r->id)>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @endif
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_status">الحالة</label>
                            <select id="add_status" name="status" class="form-control">
                                <option value="active" @selected(old('status', 'active') === 'active')>نشط</option>
                                <option value="disabled" @selected(old('status') === 'disabled')>معطّل</option>
                                <option value="suspended" @selected(old('status') === 'suspended')>موقوف</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group d-flex align-items-end">
                            <label class="mb-3">
                                <input type="checkbox" name="two_factor_enabled" value="1" @checked(old('two_factor_enabled', '1'))>
                                تفعيل OTP
                            </label>
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
@endsection
@section('js')
@if($openAddModal)
<script>
$(function () { $('#addUserModal').modal('show'); });
</script>
@endif
@endsection
