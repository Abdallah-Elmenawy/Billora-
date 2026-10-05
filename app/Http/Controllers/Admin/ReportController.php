<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\JournalLine;
use App\Models\MoneyEntry;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->period($request);
        $rows = SalesInvoice::with('customer')->whereBetween('invoice_date', [$from, $to])->whereIn('status', ['confirmed', 'partial', 'paid'])->latest('invoice_date')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير المبيعات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'العميل', 'التاريخ', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة'],
            'rows' => $rows->map(fn ($r) => [$r->number, $r->customer->name, $r->invoice_date->format('Y-m-d'), money($r->total), money($r->paid), money($r->remaining), invoice_status_label($r->status)]),
            'total' => money($rows->sum('total')),
        ]);
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->period($request);
        $rows = PurchaseInvoice::with('supplier')->whereBetween('invoice_date', [$from, $to])->whereIn('status', ['confirmed', 'partial', 'paid'])->latest('invoice_date')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير المشتريات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'المورد', 'التاريخ', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة'],
            'rows' => $rows->map(fn ($r) => [$r->number, $r->supplier->name, $r->invoice_date->format('Y-m-d'), money($r->total), money($r->paid), money($r->remaining), invoice_status_label($r->status)]),
            'total' => money($rows->sum('total')),
        ]);
    }

    public function inventory()
    {
        $products = Product::with('category')->where('type', 'product')->orderBy('name')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير المخزون', 'from' => null, 'to' => null,
            'headers' => ['SKU', 'المنتج', 'التصنيف', 'المخزون', 'الحد الأدنى', 'القيمة', 'تنبيه'],
            'rows' => $products->map(fn ($p) => [$p->sku, $p->name, $p->category->name ?? '-', $p->current_stock, $p->min_stock, money($p->current_stock * $p->cost_price), $p->isLowStock() ? 'منخفض' : '-']),
            'total' => money($products->sum(fn ($p) => $p->current_stock * $p->cost_price)),
        ]);
    }

    public function customers()
    {
        $customers = Customer::query()->orderByDesc('current_balance')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير العملاء', 'from' => null, 'to' => null,
            'headers' => ['الكود', 'العميل', 'الهاتف', 'الرصيد'],
            'rows' => $customers->map(fn ($c) => [$c->code, $c->name, $c->phone, money($c->current_balance)]),
            'total' => money($customers->sum('current_balance')),
        ]);
    }

    public function suppliers()
    {
        $suppliers = Supplier::query()->orderByDesc('current_balance')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير الموردين', 'from' => null, 'to' => null,
            'headers' => ['الكود', 'المورد', 'الهاتف', 'الرصيد'],
            'rows' => $suppliers->map(fn ($s) => [$s->code, $s->name, $s->phone, money($s->current_balance)]),
            'total' => money($suppliers->sum('current_balance')),
        ]);
    }

    public function expenses(Request $request)
    {
        [$from, $to] = $this->period($request);
        $rows = MoneyEntry::with('category', 'treasury')->whereBetween('entry_date', [$from, $to])->latest('entry_date')->get();

        return view('admin.reports.table', [
            'title' => 'تقرير المصروفات والإيرادات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'النوع', 'التصنيف', 'الخزينة', 'المبلغ', 'التاريخ'],
            'rows' => $rows->map(fn ($r) => [$r->number, $r->type === 'expense' ? 'مصروف' : 'إيراد', $r->category->name ?? '-', $r->treasury->name ?? '-', money($r->amount), $r->entry_date->format('Y-m-d')]),
            'total' => 'مصروف '.money($rows->where('type', 'expense')->sum('amount')).' / إيراد '.money($rows->where('type', 'revenue')->sum('amount')),
        ]);
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->period($request);
        $sales = (float) SalesInvoice::query()->whereBetween('invoice_date', [$from, $to])->whereIn('status', ['confirmed', 'partial', 'paid'])->sum('total');
        $purchases = (float) PurchaseInvoice::query()->whereBetween('invoice_date', [$from, $to])->whereIn('status', ['confirmed', 'partial', 'paid'])->sum('total');
        $expenses = (float) MoneyEntry::query()->where('type', 'expense')->whereBetween('entry_date', [$from, $to])->sum('amount');
        $revenues = (float) MoneyEntry::query()->where('type', 'revenue')->whereBetween('entry_date', [$from, $to])->sum('amount');

        return view('admin.reports.profit', compact('from', 'to', 'sales', 'purchases', 'expenses', 'revenues'));
    }

    public function financial()
    {
        return view('admin.reports.financial', ['accounts' => Account::query()->orderBy('code')->get()->groupBy('type')]);
    }

    public function accountLedger(Request $request)
    {
        $accounts = Account::query()->orderBy('code')->get();
        $account = $request->account_id ? Account::query()->find($request->account_id) : null;
        $lines = $account ? JournalLine::with('journal')->where('account_id', $account->id)->latest()->paginate(30) : collect();

        return view('admin.reports.ledger', compact('accounts', 'account', 'lines'));
    }

    protected function period(Request $request): array
    {
        return [$request->input('from', now()->startOfMonth()->toDateString()), $request->input('to', now()->toDateString())];
    }
}
