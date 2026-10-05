@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', ['title' => 'قيد يومية'])
@endsection
@section('content')
<div class="card"><div class="card-body">
<form method="post" action="{{ route('journals.store') }}">@csrf
    <div class="row">
        <div class="col-md-4 form-group"><label>التاريخ</label><input type="date" name="journal_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
        <div class="col-md-8 form-group"><label>الوصف</label><input name="description" class="form-control" required></div>
    </div>
    <table class="table" id="lines">
        <thead><tr><th class="col-serial">#</th><th>الحساب</th><th>مدين</th><th>دائن</th></tr></thead>
        <tbody>
        @for($i=0;$i<4;$i++)
            <tr>
                <td class="col-serial">{{ $i + 1 }}</td>
                <td><select name="lines[{{ $i }}][account_id]" class="form-control">
                    <option value="">—</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>@endforeach
                </select></td>
                <td><input name="lines[{{ $i }}][debit]" type="number" step="0.01" class="form-control" value="0"></td>
                <td><input name="lines[{{ $i }}][credit]" type="number" step="0.01" class="form-control" value="0"></td>
            </tr>
        @endfor
        </tbody>
    </table>
    <button class="btn btn-primary">ترحيل القيد</button>
</form>
</div></div>
@endsection
