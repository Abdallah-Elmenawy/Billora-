<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoneyCategory;
use App\Models\MoneyEntry;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('admin.expenses.index', [
            'entries' => MoneyEntry::with('category', 'treasury')->latest()->paginate(15),
            'categories' => MoneyCategory::query()->orderBy('name')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request, AccountingService $accounting)
    {
        try {
            $accounting->recordMoneyEntry($this->validated($request));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل العملية.');
    }

    public function edit(MoneyEntry $entry)
    {
        return view('admin.expenses.edit', [
            'entry' => $entry,
            'categories' => MoneyCategory::query()->orderBy('name')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, MoneyEntry $entry, AccountingService $accounting)
    {
        try {
            $accounting->updateMoneyEntry($entry, $this->validated($request));
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('expenses.index')->with('success', 'تم تحديث العملية.');
    }

    public function destroy(MoneyEntry $entry, AccountingService $accounting)
    {
        try {
            $accounting->deleteMoneyEntry($entry);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حذف العملية.');
    }

    public function storeCategory(Request $request)
    {
        MoneyCategory::query()->create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:expense,revenue'],
            'account_id' => ['nullable', 'exists:accounts,id'],
        ]));

        return back()->with('success', 'تم إضافة التصنيف.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:expense,revenue'],
            'money_category_id' => ['required', 'exists:money_categories,id'],
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
