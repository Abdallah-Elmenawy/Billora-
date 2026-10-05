@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'إعدادات الشركة'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="post" action="{{ route('settings.update') }}">@csrf @method('PUT')
    <div class="row">
        <div class="col-md-4 form-group"><label>اسم الشركة</label><input name="name" class="form-control" value="{{ $setting->name }}" required></div>
        <div class="col-md-4 form-group"><label>الاسم القانوني</label><input name="legal_name" class="form-control" value="{{ $setting->legal_name }}"></div>
        <div class="col-md-4 form-group"><label>البريد</label><input name="email" class="form-control" value="{{ $setting->email }}"></div>
        <div class="col-md-4 form-group"><label>الهاتف</label><input name="phone" class="form-control" value="{{ $setting->phone }}"></div>
        <div class="col-md-4 form-group"><label>الرقم الضريبي</label><input name="tax_number" class="form-control" value="{{ $setting->tax_number }}"></div>
        <div class="col-md-4 form-group"><label>العملة</label><input name="currency" class="form-control" value="{{ $setting->currency }}"></div>
        <div class="col-md-4 form-group"><label>رمز العملة</label><input name="currency_symbol" class="form-control" value="{{ $setting->currency_symbol }}"></div>
        <div class="col-md-4 form-group"><label>اللغة</label>
            <select name="language" class="form-control"><option value="ar" @selected($setting->language==='ar')>العربية</option><option value="en" @selected($setting->language==='en')>English</option></select>
        </div>
        <div class="col-md-12 form-group"><label>العنوان</label><input name="address" class="form-control" value="{{ $setting->address }}"></div>
        @foreach(['sale_prefix'=>'بادئة المبيعات','purchase_prefix'=>'بادئة المشتريات','payment_prefix'=>'بادئة الصرف','receipt_prefix'=>'بادئة التحصيل','journal_prefix'=>'بادئة القيود'] as $f=>$l)
            <div class="col-md-4 form-group"><label>{{ $l }}</label><input name="{{ $f }}" class="form-control" value="{{ $setting->$f }}" required></div>
        @endforeach
        <div class="col-md-4 form-group"><label>نسبة الضريبة</label><input name="default_tax_rate" class="form-control" value="{{ $setting->default_tax_rate }}"></div>
        <div class="col-md-4 form-group"><label><input type="checkbox" name="tax_enabled" value="1" @checked($setting->tax_enabled)> تفعيل الضريبة</label></div>
        <div class="col-md-4 form-group"><label><input type="checkbox" name="discount_enabled" value="1" @checked($setting->discount_enabled)> تفعيل الخصم</label></div>
        @foreach([
            'sales_account_id'=>'حساب المبيعات','purchases_account_id'=>'حساب المشتريات','inventory_account_id'=>'حساب المخزون',
            'cogs_account_id'=>'تكلفة المبيعات','customers_account_id'=>'حساب العملاء','suppliers_account_id'=>'حساب الموردين',
            'tax_account_id'=>'حساب الضريبة','sales_discount_account_id'=>'خصم مسموح به'
        ] as $f=>$l)
            <div class="col-md-6 form-group"><label>{{ $l }}</label>
                <select name="{{ $f }}" class="form-control">
                    <option value="">—</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}" @selected($setting->$f==$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                </select>
            </div>
        @endforeach
    </div>
    <div class="form-actions">
        <button class="btn btn-primary">حفظ الإعدادات</button>
    </div>
</form>
</div></div>
@endsection
