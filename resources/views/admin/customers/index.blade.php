@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'العملاء',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal"><i class="fe fe-plus ml-1"></i> إضافة عميل جديد</button>',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'customer'; @endphp

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-7 form-group">
                <label>بحث</label>
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="أدخل جزء من الاسم أو الموبايل...">
            </div>
            <div class="col-md-3 form-group">
                <label>الحالة</label>
                <select name="status" class="form-control">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status')==='active')>نشط</option>
                    <option value="inactive" @selected(request('status')==='inactive')>غير نشط</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <button class="btn btn-primary btn-block"><i class="fe fe-search ml-1"></i> بحث</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> العملاء</h4>
        <span class="count-badge">{{ $customers->total() }} عميل</span>
    </div>
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
                    <td class="text-muted">{{ $c->code }}</td>
                    <td class="cell-strong"><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td dir="ltr">{{ $c->phone ?: '-' }}</td>
                    <td>{{ $c->address ?: '-' }}</td>
                    <td class="cell-money {{ $c->current_balance > 0 ? 'is-neg' : '' }}">{{ money($c->current_balance) }}</td>
                    <td>{{ $c->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('customers.show', $c)" title="كشف الحساب" />
                            <x-view-link :href="route('sales.index', ['customer_id' => $c->id])" title="فواتير العميل" icon="fe-file-text" class="btn-icon-print" />
                            <x-edit-link :href="route('customers.edit', $c)" />
                            <x-delete-form :action="route('customers.destroy', $c)" message="هل أنت متأكد من حذف هذا العميل؟ سيتم إزالة بياناته من النظام." title="حذف العميل" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا يوجد عملاء</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $customers->firstItem() }} - {{ $customers->lastItem() }} من {{ $customers->total() }}</span>
            {{ $customers->withQueryString()->links() }}
        </div>
    @endif
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
