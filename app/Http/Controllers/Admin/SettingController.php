<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'setting' => CompanySetting::current(),
            'accounts' => Account::query()->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'language' => ['required', 'in:ar,en'],
            'sale_prefix' => ['required', 'string', 'max:10'],
            'purchase_prefix' => ['required', 'string', 'max:10'],
            'payment_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'journal_prefix' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0'],
            'sales_account_id' => ['nullable', 'exists:accounts,id'],
            'purchases_account_id' => ['nullable', 'exists:accounts,id'],
            'inventory_account_id' => ['nullable', 'exists:accounts,id'],
            'cogs_account_id' => ['nullable', 'exists:accounts,id'],
            'customers_account_id' => ['nullable', 'exists:accounts,id'],
            'suppliers_account_id' => ['nullable', 'exists:accounts,id'],
            'tax_account_id' => ['nullable', 'exists:accounts,id'],
            'sales_discount_account_id' => ['nullable', 'exists:accounts,id'],
        ]);
        $data['tax_enabled'] = $request->boolean('tax_enabled');
        $data['discount_enabled'] = $request->boolean('discount_enabled');
        CompanySetting::current()->update($data);
        Cache::forget('company_settings');

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
