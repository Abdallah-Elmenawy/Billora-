@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'العملاء',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal"><i class="fe fe-plus ml-1"></i> إضافة عميل جديد</button>',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'customer'; @endphp

<div class="card">
    <div class="card-body">
        <form class="form-inline mb-3" method="get">
            <input name="q" value="{{ request('q') }}" class="form-control ml-2" placeholder="أدخل جزء من الاسم أو الموبايل...">
            <select name="status" class="form-control ml-2">
                <option value="">كل الحالات</option>
                <option value="active" @selected(request('status')==='active')>نشط</option>
                <option value="inactive" @selected(request('status')==='inactive')>غير نشط</option>
            </select>
            <button class="btn btn-primary">بحث</button>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>الكود</th>
                        <th>اسم العميل</th>
                        <th>الموبايل</th>
                        <th>العنوان</th>
                        <th>الرصيد</th>
                        <th>أضيف بواسطة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td class="col-serial">{{ row_no($customers, $loop) }}</td>
                        <td>{{ $c->code }}</td>
                        <td><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                        <td>{{ $c->phone ?: '-' }}</td>
                        <td>{{ $c->address ?: '-' }}</td>
                        <td>{{ money($c->current_balance) }}</td>
                        <td>{{ $c->creator->name ?? 'غير محدد' }}</td>
                        <td>{{ optional($c->created_at)->format('Y-m-d') }}</td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <a href="{{ route('sales.index') }}" class="btn btn-icon-action btn-icon-view" title="إنشاء فاتورة">
                                    <i class="fe fe-file-plus"></i><span class="sr-only">إنشاء فاتورة</span>
                                </a>
                                <x-edit-link :href="route('customers.edit', $c)" />
                                <x-delete-form :action="route('customers.destroy', $c)" message="هل أنت متأكد من حذف هذا العميل؟ سيتم إزالة بياناته من النظام." title="حذف العميل" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">لا يوجد عملاء</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $customers->links() }}</div>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('customers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="customer">
                <input type="hidden" name="status" value="active">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalTitle">إضافة عميل جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="form-group">
                        <label for="add_customer_name">اسم العميل</label>
                        <input id="add_customer_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="add_customer_phone">رقم الموبايل</label>
                        <input id="add_customer_phone" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="form-group">
                        <label for="add_customer_address">العنوان (اختياري)</label>
                        <textarea id="add_customer_address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">إضافة العميل</button>
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
$(function () { $('#addCustomerModal').modal('show'); });
</script>
@endif
@endsection
