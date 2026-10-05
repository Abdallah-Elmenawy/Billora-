<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MoneyTransaction;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\TreasuryTransfer;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class TreasuryController extends Controller
{
    public function index()
    {
        return view('admin.treasury.index', [
            'treasuriesAll' => Treasury::with('account')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
            'customers' => Customer::query()->where('status', 'active')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('status', 'active')->orderBy('name')->get(),
            'saleInvoices' => SalesInvoice::query()->whereIn('status', ['confirmed', 'partial'])->get(),
            'purchaseInvoices' => PurchaseInvoice::query()->whereIn('status', ['confirmed', 'partial'])->get(),
            'receipts' => MoneyTransaction::with('treasury', 'party')->where('type', 'receipt')->latest()->limit(8)->get(),
            'payments' => MoneyTransaction::with('treasury', 'party')->where('type', 'payment')->latest()->limit(8)->get(),
            'transfers' => TreasuryTransfer::with('fromTreasury', 'toTreasury')->latest()->limit(8)->get(),
        ]);
    }

    public function storeTreasury(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:cash,bank'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);
        Treasury::query()->create($data + ['current_balance' => $data['opening_balance'] ?? 0, 'is_active' => true]);

        return back()->with('success', 'تم إضافة الخزينة.');
    }

    public function receipt(Request $request, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank,transfer'],
            'notes' => ['nullable', 'string'],
        ]);
        try {
            $accounting->recordReceipt($data + ['party_type' => Customer::class, 'party_id' => $data['customer_id']]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    public function payment(Request $request, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank,transfer'],
            'notes' => ['nullable', 'string'],
        ]);
        try {
            $accounting->recordPayment($data + ['party_type' => Supplier::class, 'party_id' => $data['supplier_id']]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الصرف.');
    }

    public function transfer(Request $request, AccountingService $accounting)
    {
        try {
            $accounting->transfer($request->validate([
                'from_treasury_id' => ['required', 'exists:treasuries,id'],
                'to_treasury_id' => ['required', 'exists:treasuries,id', 'different:from_treasury_id'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'transferred_at' => ['required', 'date'],
                'notes' => ['nullable', 'string'],
            ]));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم التحويل بين الخزائن.');
    }
}
