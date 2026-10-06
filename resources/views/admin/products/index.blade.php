@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المنتجات',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal"><i class="fe fe-plus ml-1"></i> إضافة منتج</button>',
    ])
@endsection
@section('content')
@php $openAddModal = $errors->any(); @endphp

<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row align-items-end">
            <div class="col-md-10 form-group">
                <label>بحث</label>
                <input name="q" value="{{ request('q') }}" class="form-control" placeholder="ابحث بالاسم أو SKU...">
            </div>
            <div class="col-md-2 form-group">
                <button class="btn btn-primary btn-block"><i class="fe fe-search ml-1"></i> بحث</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-package"></i> المنتجات</h4>
        <span class="count-badge">{{ $products->total() }} منتج</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>SKU</th>
                    <th>الاسم</th>
                    <th>التصنيف</th>
                    <th>النوع</th>
                    <th>التكلفة</th>
                    <th>البيع</th>
                    <th>المخزون</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr class="{{ $p->isLowStock() ? 'table-warning' : '' }}">
                    <td class="col-serial">{{ row_no($products, $loop) }}</td>
                    <td class="text-muted">{{ $p->sku }}</td>
                    <td class="cell-strong">{{ $p->name }}</td>
                    <td>{{ $p->category->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $p->type === 'service' ? 'info' : 'light' }}">{{ $p->type === 'service' ? 'خدمة' : 'منتج' }}</span></td>
                    <td class="cell-money">{{ money($p->cost_price) }}</td>
                    <td class="cell-money is-pos">{{ money($p->sale_price) }}</td>
                    <td>
                        {{ $p->current_stock }}
                        @if($p->isLowStock())<span class="badge badge-warning mr-1">منخفض</span>@endif
                    </td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-edit-link :href="route('products.edit', $p)" />
                            <x-delete-form :action="route('products.destroy', $p)" message="هل أنت متأكد من حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء." title="حذف المنتج" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="cell-empty text-center text-muted"><i class="fe fe-package"></i>لا توجد منتجات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer table-card-footer">
            <span class="text-muted">عرض {{ $products->firstItem() }} - {{ $products->lastItem() }} من {{ $products->total() }}</span>
            {{ $products->withQueryString()->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('products.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalTitle">إضافة منتج</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_sku">SKU</label>
                            <input id="add_sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required>
                            @error('sku')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_name">الاسم</label>
                            <input id="add_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_category">التصنيف</label>
                            <select id="add_category" name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_type">النوع</label>
                            <select id="add_type" name="type" class="form-control">
                                <option value="product" @selected(old('type', 'product') === 'product')>منتج</option>
                                <option value="service" @selected(old('type') === 'service')>خدمة</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_unit">الوحدة</label>
                            <input id="add_unit" name="unit" class="form-control" value="{{ old('unit', 'قطعة') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_cost_price">سعر التكلفة</label>
                            <input id="add_cost_price" name="cost_price" type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price') }}">
                            @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_sale_price">سعر البيع</label>
                            <input id="add_sale_price" name="sale_price" type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price') }}" required>
                            @error('sale_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_min_stock">الحد الأدنى</label>
                            <input id="add_min_stock" name="min_stock" type="number" step="0.001" class="form-control" value="{{ old('min_stock') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_current_stock">المخزون الحالي</label>
                            <input id="add_current_stock" name="current_stock" type="number" step="0.001" class="form-control" value="{{ old('current_stock') }}">
                        </div>
                        <div class="col-md-6 form-group d-flex align-items-end">
                            <label class="mb-3">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                                نشط
                            </label>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="add_description">الوصف</label>
                            <textarea id="add_description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
@if($openAddModal)
<script>
$(function () { $('#addProductModal').modal('show'); });
</script>
@endif
@endsection
