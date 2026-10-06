@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'تعديل الدور',
        'subtitle' => $role->name,
        'action' => '<a href="'.route('roles.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الأدوار</a>',
    ])
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-shield"></i> {{ $role->name }}</h4>
        <span class="count-badge">{{ $role->permissions->count() }} صلاحية</span>
    </div>
    <div class="card-body p-4">
        @if($role->slug === 'admin')
            <div class="alert alert-info">دور مدير النظام يملك كل الصلاحيات دائمًا، ولا يمكن تقييده.</div>
        @endif
        <form method="post" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">اسم الدور</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="description">الوصف</label>
                    <input id="description" name="description" class="form-control" value="{{ old('description', $role->description) }}">
                </div>
            </div>

            <h5 class="mb-3">الصلاحيات</h5>
            @include('admin.roles._permissions', [
                'selected' => old('permissions', $role->permissions->pluck('id')->all()),
                'locked' => $role->slug === 'admin',
            ])

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="{{ route('roles.index') }}" class="btn btn-light">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
