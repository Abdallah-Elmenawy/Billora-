@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل المستخدم'])
@endsection
@section('css')
<style>
    .user-edit-page .perm-card {
        background: #F8FAFC;
        border: 1px solid #EEF1F6;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
        text-align: right;
    }
    .user-edit-page .perm-card h6 {
        margin-bottom: 0.75rem;
        font-weight: 800;
        color: #2D3E50;
    }
    .user-edit-page .perm-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0.45rem;
        font-weight: 600;
        color: #1F2937;
        font-size: 14px;
    }
    .user-edit-page .otp-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #F0F1F3;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        min-height: 48px;
        font-weight: 700;
        color: #2D3E50;
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="user-edit-page">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <form method="post" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="name">الاسم</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="username">اسم المستخدم</label>
                        <input id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}">
                        @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="email">البريد الالكتروني</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="phone">الهاتف</label>
                        <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="password">كلمة مرور جديدة</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="اتركها فارغة للإبقاء على الحالية">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="role_id">الدور</label>
                        <select id="role_id" name="role_id" class="form-control">
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id) == $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="status">الحالة</label>
                        <select id="status" name="status" class="form-control">
                            @foreach(['active' => 'نشط', 'disabled' => 'معطّل', 'suspended' => 'موقوف'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', $user->status) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>التحقق بخطوتين</label>
                        <label class="otp-check mb-0">
                            <input type="checkbox" name="two_factor_enabled" value="1" @checked(old('two_factor_enabled', $user->two_factor_enabled))>
                            تفعيل OTP
                        </label>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">صلاحيات إضافية</h4>
                <div class="row">
                    @foreach($permissions as $module => $items)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="perm-card">
                                <h6>{{ $module }}</h6>
                                @foreach($items as $p)
                                    <label class="perm-item">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(collect(old('permissions', $user->extraPermissions->pluck('id')->all()))->contains($p->id))>
                                        <span>{{ $p->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light">رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
