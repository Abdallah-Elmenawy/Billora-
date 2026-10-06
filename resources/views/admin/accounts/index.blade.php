@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'دليل الحسابات',
        'action' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccountModal"><i class="fe fe-plus ml-1"></i> حساب جديد</button>',
    ])
@endsection
@section('content')
@php
    $openAddModal = old('_form') === 'account';
    $typeLabels = [
        'asset' => 'أصل',
        'liability' => 'التزام',
        'equity' => 'ملكية',
        'revenue' => 'إيراد',
        'expense' => 'مصروف',
    ];
@endphp

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-layers"></i> الحسابات</h4>
        <span class="count-badge">{{ $accounts->count() }} حساب</span>
    </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="col-serial">#</th>
                        <th>الكود</th>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>الحساب الأب</th>
                        <th>الرصيد</th>
                        <th>الحالة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($accounts as $a)
                    <tr>
                        <td class="col-serial">{{ row_no($accounts, $loop) }}</td>
                        <td class="cell-strong">{{ $a->code }}</td>
                        <td>
                            <form method="post" action="{{ route('accounts.update', $a) }}" id="account-form-{{ $a->id }}" class="d-inline-flex align-items-center">
                                @csrf
                                @method('PUT')
                                <input name="name" class="form-control account-name-input" value="{{ $a->name }}" required>
                            </form>
                        </td>
                        <td><span class="badge badge-light">{{ $typeLabels[$a->type] ?? $a->type }}</span></td>
                        <td>{{ $a->parent->name ?? '-' }}</td>
                        <td class="cell-money">{{ money($a->current_balance) }}</td>
                        <td>
                            <label class="mb-0 account-active-toggle">
                                <input type="checkbox" name="is_active" value="1" form="account-form-{{ $a->id }}" @checked($a->is_active)>
                                <span>نشط</span>
                            </label>
                        </td>
                        <td class="col-actions">
                            <div class="btn-actions">
                                <button type="submit" form="account-form-{{ $a->id }}" class="btn btn-icon-action btn-icon-edit" title="حفظ التعديل">
                                    <i class="fe fe-check"></i>
                                    <span class="sr-only">حفظ التعديل</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="cell-empty text-center text-muted"><i class="fe fe-layers"></i>لا توجد حسابات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
</div>

<div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('accounts.store') }}">
                @csrf
                <input type="hidden" name="_form" value="account">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalTitle">حساب جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_account_code">الكود</label>
                            <input id="add_account_code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="مثال: 1500" required>
                            @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_account_name">الاسم</label>
                            <input id="add_account_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_account_type">النوع</label>
                            <select id="add_account_type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                @foreach($typeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', 'asset') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_account_parent">الحساب الأب</label>
                            <select id="add_account_parent" name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                                <option value="">بدون أب</option>
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}" @selected(old('parent_id') == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label for="add_account_opening">رصيد أول المدة</label>
                            <input id="add_account_opening" name="opening_balance" type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" value="{{ old('opening_balance', 0) }}">
                            @error('opening_balance')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ الحساب</button>
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
$(function () { $('#addAccountModal').modal('show'); });
</script>
@endif
@endsection
