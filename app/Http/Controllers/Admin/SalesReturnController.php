<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesReturnController extends Controller
{
    public function index()
    {
        $invoices = SalesInvoice::with('customer', 'items.product')
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->latest()->get();

        return view('admin.sales_returns.index', [
            'returns' => SalesReturn::with('customer', 'invoice', 'creator')->latest()->paginate(15),
            'invoices' => $invoices,
            'invoiceItemsJson' => $invoices->map(fn ($inv) => [
                'id' => $inv->id,
                'items' => $inv->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? '-',
                    'qty' => $item->qty,
                    'price' => $item->unit_price,
                ])->values(),
            ])->values(),
        ]);
    }

    public function create()
    {
        return redirect()->route('sales-returns.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
        $invoice = SalesInvoice::query()->findOrFail($data['sales_invoice_id']);
        $ret = DB::transaction(function () use ($data, $invoice) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_price'];
            }
            $ret = SalesReturn::query()->create([
                'number' => CompanySetting::current()->nextNumber('sale').'-R',
                'sales_invoice_id' => $invoice->id, 'customer_id' => $invoice->customer_id,
                'return_date' => $data['return_date'], 'status' => 'draft', 'total' => $total,
                'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ]);
            foreach ($data['items'] as $item) {
                $ret->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_price']]);
            }

            return $ret;
        });

        return redirect()->route('sales-returns.show', $ret)->with('success', 'تم حفظ المرتجع.');
    }

    public function show(SalesReturn $sales_return)
    {
        return view('admin.sales_returns.show', ['return' => $sales_return->load('customer', 'invoice', 'items.product')]);
    }

    public function destroy(SalesReturn $sales_return)
    {
        if ($sales_return->status !== 'draft') {
            return back()->with('error', 'يمكن حذف المسودة فقط.');
        }
        $sales_return->delete();

        return back()->with('success', 'تم حذف المرتجع.');
    }

    public function confirm(SalesReturn $sales_return, AccountingService $accounting)
    {
        try {
            $accounting->confirmSalesReturn($sales_return->load('items.product', 'customer'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد المرتجع.');
    }
}
