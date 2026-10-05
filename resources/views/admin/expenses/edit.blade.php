@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'تعديل عملية'])
@endsection
@section('content')
<div class="card">
    <div class="card-body p-4 p-md-5">
        <form method="post" action="{{ route('expenses.update', $entry) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="type">النوع</label>
                    <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="expense" @selected(old('type', $entry->type) === 'expense')>مصروف</option>
                        <option value="revenue" @selected(old('type', $entry->type) === 'revenue')>إيراد</option>
                    </select>
                    @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="money_category_id">التصنيف</label>
                    <select id="money_category_id" name="money_category_id" class="form-control @error('money_category_id') is-invalid @enderror" required>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('money_category_id', $entry->money_category_id) == $c->id)>{{ $c->name }} ({{ $c->type === 'expense' ? 'مصروف' : 'إيراد' }})</option>
                        @endforeach
                    </select>
                    @error('money_category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="treasury_id">الخزينة</label>
                    <select id="treasury_id" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                        @foreach($treasuries as $t)
                            <option value="{{ $t->id }}" @selected(old('treasury_id', $entry->treasury_id) == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="amount">المبلغ</label>
                    <input id="amount" name="amount" type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $entry->amount) }}" required>
                    @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="entry_date">التاريخ</label>
                    <input id="entry_date" name="entry_date" type="date" class="form-control @error('entry_date') is-invalid @enderror" value="{{ old('entry_date', optional($entry->entry_date)->format('Y-m-d')) }}" required>
                    @error('entry_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-group">
                    <label for="description">البيان</label>
                    <input id="description" name="description" class="form-control" value="{{ old('description', $entry->description) }}">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-light">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
