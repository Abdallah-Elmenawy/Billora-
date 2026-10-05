@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل منتج'])
@endsection
@section('content')
<div class="card">
    <div class="card-body p-4 p-md-5">
        <form method="post" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="sku">SKU</label>
                    <input id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" required>
                    @error('sku')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-group">
                    <label for="name">الاسم</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-group">
                    <label for="category_id">التصنيف</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">—</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="type">النوع</label>
                    <select id="type" name="type" class="form-control">
                        <option value="product" @selected(old('type', $product->type) === 'product')>منتج</option>
                        <option value="service" @selected(old('type', $product->type) === 'service')>خدمة</option>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="unit">الوحدة</label>
                    <input id="unit" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'قطعة') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="cost_price">سعر التكلفة</label>
                    <input id="cost_price" name="cost_price" type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price) }}">
                    @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-group">
                    <label for="sale_price">سعر البيع</label>
                    <input id="sale_price" name="sale_price" type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price', $product->sale_price) }}" required>
                    @error('sale_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-group">
                    <label for="min_stock">الحد الأدنى</label>
                    <input id="min_stock" name="min_stock" type="number" step="0.001" class="form-control" value="{{ old('min_stock', $product->min_stock) }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="current_stock">المخزون الحالي</label>
                    <input id="current_stock" name="current_stock" type="number" step="0.001" class="form-control" value="{{ old('current_stock', $product->current_stock) }}">
                </div>
                <div class="col-md-4 form-group d-flex align-items-end">
                    <label class="mb-3">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                        نشط
                    </label>
                </div>
                <div class="col-md-12 form-group">
                    <label for="description">الوصف</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="{{ route('products.index') }}" class="btn btn-light">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
