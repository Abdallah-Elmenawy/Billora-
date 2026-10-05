@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل الدور'])
@endsection
@section('content')
<div class="card">
    <div class="card-body p-4 p-md-5">
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

            <h4 class="mt-2 mb-3">الصلاحيات</h4>
            <div class="row">
                @foreach($permissions as $module => $items)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="perm-card">
                            <h6>{{ $module }}</h6>
                            @foreach($items as $p)
                                <label class="perm-item">
                                    <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(collect(old('permissions', $role->permissions->pluck('id')->all()))->contains($p->id))>
                                    <span>{{ $p->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="{{ route('roles.index') }}" class="btn btn-light">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
