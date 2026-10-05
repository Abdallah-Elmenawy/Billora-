<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\MoneyEntry;
use App\Models\MoneyTransaction;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Supplier;
use App\Models\Treasury;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $posted = ['confirmed', 'partial', 'paid'];
        $sales = (float) SalesInvoice::query()->whereIn('status', $posted)->sum('total');
        $purchases = (float) PurchaseInvoice::query()->whereIn('status', $posted)->sum('total');
        $expenses = (float) MoneyEntry::query()->where('type', 'expense')->sum('amount');
        $revenues = (float) MoneyEntry::query()->where('type', 'revenue')->sum('amount');
        $profit = $sales + $revenues - $purchases - $expenses;
        $receivable = (float) Customer::query()->sum('current_balance');
        $payable = (float) Supplier::query()->sum('current_balance');
        $treasury = (float) Treasury::query()->sum('current_balance');
        $inventoryValue = (float) Product::query()->where('type', 'product')->get()->sum(fn ($p) => $p->current_stock * $p->cost_price);
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));

        $kpiItems = [
            ['title' => 'مبيعات مؤكدة', 'value' => $sales, 'count' => SalesInvoice::query()->whereIn('status', $posted)->count(), 'color' => 'blue', 'icon' => 'fe-shopping-cart'],
            ['title' => 'مشتريات مؤكدة', 'value' => $purchases, 'count' => PurchaseInvoice::query()->whereIn('status', $posted)->count(), 'color' => 'pink', 'icon' => 'fe-file-text'],
            ['title' => 'المصروفات', 'value' => $expenses, 'count' => MoneyEntry::query()->where('type', 'expense')->count(), 'color' => 'red', 'icon' => 'fe-minus-circle'],
            ['title' => 'صافي الربح', 'value' => $profit, 'count' => 0, 'color' => 'green', 'icon' => 'fe-trending-up'],
            ['title' => 'الذمم المدينة', 'value' => $receivable, 'count' => Customer::query()->where('current_balance', '>', 0)->count(), 'color' => 'cyan', 'icon' => 'fe-users'],
            ['title' => 'الذمم الدائنة', 'value' => $payable, 'count' => Supplier::query()->where('current_balance', '>', 0)->count(), 'color' => 'purple', 'icon' => 'fe-truck'],
            ['title' => 'رصيد الخزينة', 'value' => $treasury, 'count' => Treasury::query()->where('is_active', true)->count(), 'color' => 'amber', 'icon' => 'fe-credit-card'],
            ['title' => 'قيمة المخزون', 'value' => $inventoryValue, 'count' => Product::query()->where('type', 'product')->count(), 'color' => 'orange', 'icon' => 'fe-box'],
        ];
        $kpiTotal = collect($kpiItems)->sum(fn ($item) => abs((float) $item['value']));
        $kpiCards = collect($kpiItems)->map(function ($item) use ($kpiTotal) {
            $pct = $kpiTotal > 0 ? (int) round((abs((float) $item['value']) / $kpiTotal) * 100) : 0;
            $item['percent'] = $pct;
            $item['up'] = (float) $item['value'] >= 0;

            return $item;
        });

        return view('admin.dashboard', [
            'kpiCards' => $kpiCards,
            'sales' => $sales,
            'purchases' => $purchases,
            'expenses' => $expenses,
            'profit' => $sales + $revenues - $purchases - $expenses,
            'collected' => (float) SalesInvoice::query()->whereIn('status', $posted)->sum('paid'),
            'receivable' => (float) Customer::query()->sum('current_balance'),
            'payable' => (float) Supplier::query()->sum('current_balance'),
            'treasury' => (float) Treasury::query()->sum('current_balance'),
            'inventoryValue' => (float) Product::query()->where('type', 'product')->get()->sum(fn ($p) => $p->current_stock * $p->cost_price),
            'latestSales' => SalesInvoice::with('customer')->latest()->limit(6)->get(),
            'latestPayments' => MoneyTransaction::with('party', 'treasury')->latest()->limit(6)->get(),
            'lowStock' => Product::query()->where('type', 'product')->whereColumn('current_stock', '<=', 'min_stock')->limit(6)->get(),
            'activities' => ActivityLog::with('user')->latest()->limit(8)->get(),
            'chartLabels' => $days->map(fn ($d) => $d->format('m-d'))->values(),
            'chartSales' => $days->map(fn ($d) => (float) SalesInvoice::query()->whereDate('invoice_date', $d)->whereIn('status', $posted)->sum('total'))->values(),
            'chartPurchases' => $days->map(fn ($d) => (float) PurchaseInvoice::query()->whereDate('invoice_date', $d)->whereIn('status', $posted)->sum('total'))->values(),
            'chartExpenses' => $days->map(fn ($d) => (float) MoneyEntry::query()->whereDate('entry_date', $d)->where('type', 'expense')->sum('amount'))->values(),
            'chartRevenues' => $days->map(fn ($d) => (float) MoneyEntry::query()->whereDate('entry_date', $d)->where('type', 'revenue')->sum('amount'))->values(),
            'topProducts' => SalesInvoiceItem::query()
                ->select('product_id', DB::raw('SUM(qty) as qty_sold'), DB::raw('SUM(total) as amount'))
                ->whereHas('invoice', fn ($q) => $q->whereIn('status', $posted))
                ->groupBy('product_id')->orderByDesc('qty_sold')->with('product')->limit(5)->get(),
        ]);
    }
}
