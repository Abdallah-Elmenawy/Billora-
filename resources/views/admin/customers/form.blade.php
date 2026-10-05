@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => $customer->exists ? 'تعديل عميل' : 'إضافة عميل'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="post" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}">
    @csrf @if($customer->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-md-4 form-group"><label>الكود</label><input name="code" class="form-control" value="{{ old('code', $customer->code) }}"></div>
        <div class="col-md-4 form-group"><label>الاسم *</label><input name="name" class="form-control" required value="{{ old('name', $customer->name) }}"></div>
        <div class="col-md-4 form-group"><label>الهاتف</label><input name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}"></div>
        <div class="col-md-4 form-group"><label>البريد</label><input name="email" type="email" class="form-control" value="{{ old('email', $customer->email) }}"></div>
        <div class="col-md-4 form-group"><label>الرقم الضريبي</label><input name="tax_number" class="form-control" value="{{ old('tax_number', $customer->tax_number) }}"></div>
        <div class="col-md-4 form-group"><label>الحالة</label>
            <select name="status" class="form-control">
                <option value="active" @selected(old('status', $customer->status ?? 'active')==='active')>نشط</option>
                <option value="inactive" @selected(old('status', $customer->status)==='inactive')>غير نشط</option>
            </select>
        </div>
        <div class="col-md-4 form-group"><label>رصيد أول المدة</label><input name="opening_balance" type="number" step="0.01" class="form-control" value="{{ old('opening_balance', $customer->opening_balance) }}"></div>
        <div class="col-md-4 form-group"><label>حد الائتمان</label><input name="credit_limit" type="number" step="0.01" class="form-control" value="{{ old('credit_limit', $customer->credit_limit) }}"></div>
        <div class="col-md-12 form-group"><label>العنوان</label><input name="address" class="form-control" value="{{ old('address', $customer->address) }}"></div>
        <div class="col-md-12 form-group"><label>ملاحظات</label><textarea name="notes" class="form-control">{{ old('notes', $customer->notes) }}</textarea></div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary">حفظ</button>
        <a href="{{ route('customers.index') }}" class="btn btn-light">رجوع</a>
    </div>
</form>
</div></div>
@endsection
