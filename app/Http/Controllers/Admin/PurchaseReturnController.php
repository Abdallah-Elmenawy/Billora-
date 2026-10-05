<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with('supplier', 'items.product')
            ->whereIn('status', ['confirmed', 'partial', 'paid'])
            ->latest()->get();

        return view('admin.purchase_returns.index', [
            'returns' => PurchaseReturn::with('supplier', 'invoice', 'creator')->latest()->paginate(15),
            'invoices' => $invoices,
            'invoiceItemsJson' => $invoices->map(fn ($inv) => [
                'id' => $inv->id,
                'items' => $inv->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? '-',
                    'qty' => $item->qty,
                    'price' => $item->unit_cost,
                ])->values(),
            ])->values(),
        ]);
    }

    public function create()
    {
        return redirect()->route('purchase-returns.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_invoice_id' => ['required', 'exists:purchase_invoices,id'],
            'return_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
        $invoice = PurchaseInvoice::query()->findOrFail($data['purchase_invoice_id']);
        $ret = DB::transaction(function () use ($data, $invoice) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_cost'];
            }
            $ret = PurchaseReturn::query()->create([
                'number' => CompanySetting::current()->nextNumber('purchase').'-R',
                'purchase_invoice_id' => $invoice->id, 'supplier_id' => $invoice->supplier_id,
                'return_date' => $data['return_date'], 'status' => 'draft', 'total' => $total,
                'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ]);
            foreach ($data['items'] as $item) {
                $ret->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_cost']]);
            }

            return $ret;
        });

        return redirect()->route('purchase-returns.show', $ret)->with('success', 'تم حفظ المرتجع.');
    }

    public function show(PurchaseReturn $purchase_return)
    {
        return view('admin.purchase_returns.show', ['return' => $purchase_return->load('supplier', 'invoice', 'items.product')]);
    }

    public function destroy(PurchaseReturn $purchase_return)
    {
        if ($purchase_return->status !== 'draft') {
            return back()->with('error', 'يمكن حذف المسودة فقط.');
        }
        $purchase_return->delete();

        return back()->with('success', 'تم حذف المرتجع.');
    }

    public function confirm(PurchaseReturn $purchase_return, AccountingService $accounting)
    {
        try {
            $accounting->confirmPurchaseReturn($purchase_return->load('items.product', 'supplier'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد المرتجع.');
    }
}
