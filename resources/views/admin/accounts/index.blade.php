@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'دليل الحسابات'])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-header">حساب جديد</div><div class="card-body">
            <form method="post" action="{{ route('accounts.store') }}">@csrf
                <input name="code" class="form-control mb-2" placeholder="الكود" required>
                <input name="name" class="form-control mb-2" placeholder="الاسم" required>
                <select name="type" class="form-control mb-2">
                    <option value="asset">أصل</option><option value="liability">التزام</option>
                    <option value="equity">ملكية</option><option value="revenue">إيراد</option><option value="expense">مصروف</option>
                </select>
                <select name="parent_id" class="form-control mb-2">
                    <option value="">بدون أب</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>@endforeach
                </select>
                <input name="opening_balance" type="number" step="0.01" class="form-control mb-2" placeholder="رصيد أول المدة">
                <button class="btn btn-primary">حفظ</button>
            </form>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card"><div class="card-body table-responsive">
            <table class="table">
                <thead><tr><th class="col-serial">#</th><th>الكود</th><th>الاسم</th><th>النوع</th><th>الرصيد</th></tr></thead>
                <tbody>
                @foreach($accounts as $a)
                    <tr>
                        <td class="col-serial">{{ row_no($accounts, $loop) }}</td>
                        <td>{{ $a->code }}</td>
                        <td>
                            <form method="post" action="{{ route('accounts.update', $a) }}" class="d-flex align-items-center flex-wrap">@csrf @method('PUT')
                                <input name="name" class="form-control ml-2 mb-1" style="max-width:220px" value="{{ $a->name }}">
                                <label class="mb-1 ml-2"><input type="checkbox" name="is_active" value="1" @checked($a->is_active)> نشط</label>
                                <button type="submit" class="btn btn-icon-action btn-icon-edit mb-1" title="تحديث">
                                    <i class="fe fe-check"></i>
                                    <span class="sr-only">تحديث</span>
                                </button>
                            </form>
                        </td>
                        <td>{{ $a->type }}</td>
                        <td>{{ money($a->current_balance) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
</div>
@endsection
