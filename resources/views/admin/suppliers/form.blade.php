@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => $supplier->exists ? 'تعديل مورد' : 'إضافة مورد'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="post" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
    @csrf @if($supplier->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-md-4 form-group"><label>الكود</label><input name="code" class="form-control" value="{{ old('code', $supplier->code) }}"></div>
        <div class="col-md-4 form-group"><label>الاسم *</label><input name="name" class="form-control" required value="{{ old('name', $supplier->name) }}"></div>
        <div class="col-md-4 form-group"><label>الهاتف</label><input name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}"></div>
        <div class="col-md-4 form-group"><label>البريد</label><input name="email" type="email" class="form-control" value="{{ old('email', $supplier->email) }}"></div>
        <div class="col-md-4 form-group"><label>الرقم الضريبي</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $supplier->tax_number) }}"></div>
        <div class="col-md-4 form-group"><label>الحالة</label>
            <select name="status" class="form-control">
                <option value="active" @selected(old('status', $supplier->status ?? 'active')==='active')>نشط</option>
                <option value="inactive" @selected(old('status', $supplier->status)==='inactive')>غير نشط</option>
            </select>
        </div>
        <div class="col-md-4 form-group"><label>رصيد أول المدة</label><input name="opening_balance" type="number" step="0.01" class="form-control" value="{{ old('opening_balance', $supplier->opening_balance) }}"></div>
        <div class="col-md-12 form-group"><label>العنوان</label><input name="address" class="form-control" value="{{ old('address', $supplier->address) }}"></div>
        <div class="col-md-12 form-group"><label>ملاحظات</label><textarea name="notes" class="form-control">{{ old('notes', $supplier->notes) }}</textarea></div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary">حفظ</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-light">رجوع</a>
    </div>
</form>
</div></div>
@endsection
