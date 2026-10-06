@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الموردون',
        'action' => can('suppliers.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplierModal"><i class="fe fe-plus ml-1"></i> إضافة مورد جديد</button>' : '',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'supplier'; @endphp

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-7 form-group">
                <label>بحث</label>
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث بالاسم أو رقم الموبايل...">
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
        <h4 class="card-title"><i class="fe fe-briefcase"></i> الموردون</h4>
        <span class="count-badge">{{ $suppliers->total() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الكود</th>
                    <th>اسم المورد</th>
                    <th>الموبايل</th>
                    <th>العنوان</th>
                    <th>السجل التجاري</th>
                    <th>الرصيد</th>
                    <th>أضيف بواسطة</th>
                    <th>تاريخ الإضافة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ row_no($suppliers, $loop) }}</td>
                    <td class="text-muted">{{ $s->code }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td dir="ltr">{{ $s->phone ?: '-' }}</td>
                    <td>{{ $s->address ?: '-' }}</td>
                    <td>{{ $s->tax_number ?: '-' }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td>{{ $s->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($s->created_at)->format('Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            <x-view-link :href="route('purchases.index', ['supplier_id' => $s->id])" title="فواتير المورد" icon="fe-file-text" class="btn-icon-print" />
                            @if(can('suppliers.update'))<x-edit-link :href="route('suppliers.edit', $s)" />@endif
                            @if(can('suppliers.delete'))<x-delete-form :action="route('suppliers.destroy', $s)" message="هل أنت متأكد من حذف هذا المورد؟ سيتم إزالة بياناته من النظام." title="حذف المورد" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا يوجد موردون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $suppliers->firstItem() }} - {{ $suppliers->lastItem() }} من {{ $suppliers->total() }}</span>
            {{ $suppliers->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('suppliers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="supplier">
                <input type="hidden" name="status" value="active">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalTitle">إضافة مورد جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="form-group">
                        <label for="add_supplier_name">اسم المورد</label>
                        <input id="add_supplier_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_supplier_phone">رقم الموبايل</label>
                            <input id="add_supplier_phone" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_supplier_tax">السجل التجاري (اختياري)</label>
                            <input id="add_supplier_tax" name="tax_number" class="form-control" value="{{ old('tax_number') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_supplier_address">العنوان (اختياري)</label>
                        <textarea id="add_supplier_address" name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">إضافة المورد</button>
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
$(function () { $('#addSupplierModal').modal('show'); });
</script>
@endif
@endsection
