<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        return view('admin.accounts.index', [
            'accounts' => Account::query()->with('parent')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Account::query()->create($request->validate([
            'code' => ['required', 'string', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
        ]) + ['current_balance' => $request->input('opening_balance', 0), 'is_active' => true]);

        return back()->with('success', 'تم إضافة الحساب.');
    }

    public function update(Request $request, Account $account)
    {
        $account->update($request->validate([
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'تم تحديث الحساب.');
    }

    public function receivables()
    {
        return view('admin.accounts.receivables', [
            'customers' => Customer::query()->orderByDesc('current_balance')->get(),
        ]);
    }

    public function payables()
    {
        return view('admin.accounts.payables', [
            'suppliers' => Supplier::query()->orderByDesc('current_balance')->get(),
        ]);
    }
}
