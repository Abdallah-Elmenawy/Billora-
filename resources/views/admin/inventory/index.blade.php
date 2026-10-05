@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المخزون',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStockModal"><i class="fe fe-plus ml-1"></i> حركة مخزون يدوية</button>',
    ])
@endsection
@section('content')
@php
    $openAddModal = old('_form') === 'inventory';
    $typeLabels = ['in' => 'إضافة', 'out' => 'صرف', 'adjust' => 'تسوية'];
@endphp

<div class="card mb-3">
    <div class="card-body">
        <h5 class="mb-3 text-right">أرصدة المنتجات</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>المنتج</th>
                        <th>المخزون</th>
                        <th>الحد</th>
                        <th>القيمة</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr class="{{ $p->isLowStock() ? 'table-warning' : '' }}">
                        <td class="col-serial">{{ row_no($products, $loop) }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->current_stock }}</td>
                        <td>{{ $p->min_stock }}</td>
                        <td>{{ money($p->current_stock * $p->cost_price) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">لا توجد منتجات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3 text-right">آخر الحركات</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>المنتج</th>
                        <th>النوع</th>
                        <th>الكمية</th>
                        <th>المستخدم</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td class="col-serial">{{ row_no($movements, $loop) }}</td>
                        <td>{{ $m->product->name ?? '-' }}</td>
                        <td>{{ $typeLabels[$m->type] ?? $m->type }}</td>
                        <td>{{ $m->qty }}</td>
                        <td>{{ $m->creator->name ?? '-' }}</td>
                        <td>{{ optional($m->created_at)->format('H:i Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">لا توجد حركات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('inventory.store') }}">
                @csrf
                <input type="hidden" name="_form" value="inventory">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalTitle">حركة مخزون يدوية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    @if($formProducts->isEmpty())
                        <div class="alert alert-warning mb-0">لا توجد منتجات يمكن تحريك مخزونها.</div>
                    @else
                        <div class="form-group">
                            <label for="stock_product_id">المنتج</label>
                            <select id="stock_product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                <option value="">اختر المنتج</option>
                                @foreach($formProducts as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} ({{ $p->current_stock }})</option>
                                @endforeach
                            </select>
                            @error('product_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="stock_type">نوع الحركة</label>
                            <select id="stock_type" name="type" class="form-control">
                                <option value="in" @selected(old('type') === 'in')>إضافة</option>
                                <option value="out" @selected(old('type') === 'out')>صرف</option>
                                <option value="adjust" @selected(old('type') === 'adjust')>تسوية</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stock_qty">الكمية</label>
                            <input id="stock_qty" name="qty" type="number" step="0.001" class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty') }}" required>
                            @error('qty')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="stock_notes">ملاحظات</label>
                            <textarea id="stock_notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    @if($formProducts->isNotEmpty())
                        <button type="submit" class="btn btn-primary">تسجيل</button>
                    @endif
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
$(function () { $('#addStockModal').modal('show'); });
</script>
@endif
@endsection
