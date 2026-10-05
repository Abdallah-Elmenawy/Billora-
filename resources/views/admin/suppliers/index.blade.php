@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الموردون',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplierModal"><i class="fe fe-plus ml-1"></i> إضافة مورد جديد</button>',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'supplier'; @endphp

<div class="card">
    <div class="card-body">
        <form class="form-inline mb-3" method="get">
            <input name="q" value="{{ request('q') }}" class="form-control ml-2" placeholder="ابحث بالاسم أو رقم الموبايل...">
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
                        <td>{{ $s->code }}</td>
                        <td><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                        <td>{{ $s->phone ?: '-' }}</td>
                        <td>{{ $s->address ?: '-' }}</td>
                        <td>{{ $s->tax_number ?: '-' }}</td>
                        <td>{{ money($s->current_balance) }}</td>
                        <td>{{ $s->creator->name ?? 'غير محدد' }}</td>
                        <td>{{ optional($s->created_at)->format('Y-m-d') }}</td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <a href="{{ route('purchases.index', ['supplier_id' => $s->id]) }}" class="btn btn-icon-action btn-icon-view" title="فاتورة وارد">
                                    <i class="fe fe-file-plus"></i><span class="sr-only">فاتورة وارد</span>
                                </a>
                                <x-edit-link :href="route('suppliers.edit', $s)" />
                                <x-delete-form :action="route('suppliers.destroy', $s)" message="هل أنت متأكد من حذف هذا المورد؟ سيتم إزالة بياناته من النظام." title="حذف المورد" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">لا يوجد موردون</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $suppliers->links() }}</div>
    </div>
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
