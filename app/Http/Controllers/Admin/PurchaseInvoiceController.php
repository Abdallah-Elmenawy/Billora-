<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with('supplier', 'creator')
            ->when($request->q, fn ($q) => $q->where('number', 'like', '%'.$request->q.'%'))
            ->when($request->supplier_id, fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status));

        return view('admin.purchases.index', [
            'invoices' => (clone $query)->latest()->paginate(15)->withQueryString(),
            'displayedTotal' => (clone $query)->sum('total'),
            'grandTotal' => PurchaseInvoice::query()->where('status', '!=', 'cancelled')->sum('total'),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'formSuppliers' => Supplier::query()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'invoice' => new PurchaseInvoice(['invoice_date' => now()->toDateString()]),
            'treasuries' => Treasury::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return redirect()->route('purchases.index');
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $invoice = $this->saveInvoice($request);
        if ($request->boolean('confirm')) {
            try {
                DB::transaction(function () use ($request, $invoice, $accounting) {
                    $accounting->confirmPurchaseInvoice($invoice->fresh('items.product'));
                    $this->payIfPaid($request, $invoice, $accounting);
                });
            } catch (RuntimeException $e) {
                return redirect()->route('purchases.edit', $invoice)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('purchases.show', $invoice->fresh())->with('success', 'تم حفظ فاتورة المشتريات.');
    }

    public function show(PurchaseInvoice $purchase)
    {
        return view('admin.purchases.show', [
            'invoice' => $purchase->load(['supplier', 'items.product', 'payments.treasury']),
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(PurchaseInvoice $purchase)
    {
        if (! $purchase->isEditable()) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }

        return view('admin.purchases.form', [
            'invoice' => $purchase->load('items'),
            'suppliers' => Supplier::query()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PurchaseInvoice $purchase, AccountingService $accounting)
    {
        if (! $purchase->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }
        $invoice = $this->saveInvoice($request, $purchase);
        if ($request->boolean('confirm')) {
            try {
                DB::transaction(function () use ($request, $invoice, $accounting) {
                    $accounting->confirmPurchaseInvoice($invoice->fresh('items.product'));
                    $this->payIfPaid($request, $invoice, $accounting);
                });
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return redirect()->route('purchases.show', $invoice->fresh())->with('success', 'تم تحديث الفاتورة.');
    }

    public function confirm(PurchaseInvoice $purchase, AccountingService $accounting)
    {
        try {
            $accounting->confirmPurchaseInvoice($purchase->load('items.product'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد الفاتورة.');
    }

    public function pay(Request $request, PurchaseInvoice $purchase, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
        ]);
        try {
            $accounting->recordPayment($data + [
                'party_type' => Supplier::class, 'party_id' => $purchase->supplier_id,
                'invoice_id' => $purchase->id, 'method' => 'cash',
            ]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الصرف.');
    }

    public function print(PurchaseInvoice $purchase)
    {
        return view('admin.purchases.print', ['invoice' => $purchase->load(['supplier', 'items.product'])]);
    }

    public function cancel(PurchaseInvoice $purchase)
    {
        if ($purchase->status !== 'draft') {
            return back()->with('error', 'يمكن إلغاء المسودة فقط.');
        }
        $purchase->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء الفاتورة.');
    }

    public function destroy(PurchaseInvoice $purchase)
    {
        if ($purchase->status !== 'draft') {
            return back()->with('error', 'يمكن حذف المسودة فقط.');
        }
        $purchase->delete();

        return back()->with('success', 'تم حذف الفاتورة.');
    }

    protected function saveInvoice(Request $request, ?PurchaseInvoice $invoice = null): PurchaseInvoice
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data, $invoice) {
            $subtotal = $tax = $itemDiscount = 0;
            foreach ($data['items'] as $item) {
                $line = ((float) $item['qty'] * (float) $item['unit_cost']) - (float) ($item['discount'] ?? 0);
                $subtotal += $line;
                $tax += (float) ($item['tax'] ?? 0);
                $itemDiscount += (float) ($item['discount'] ?? 0);
            }
            $headerDiscount = (float) ($data['discount'] ?? 0);
            $total = max($subtotal + $tax - $headerDiscount, 0);
            $payload = [
                'supplier_id' => $data['supplier_id'], 'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null, 'status' => 'draft',
                'subtotal' => $subtotal + $itemDiscount, 'discount' => $headerDiscount + $itemDiscount,
                'tax' => $tax, 'total' => $total, 'paid' => 0, 'remaining' => $total,
                'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ];
            if (! $invoice) {
                $payload['number'] = CompanySetting::current()->nextNumber('purchase');
                $invoice = PurchaseInvoice::query()->create($payload);
            } else {
                $invoice->update($payload);
                $invoice->items()->delete();
            }
            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'], 'qty' => $item['qty'],
                    'unit_cost' => $item['unit_cost'], 'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => ((float) $item['qty'] * (float) $item['unit_cost']) - (float) ($item['discount'] ?? 0) + (float) ($item['tax'] ?? 0),
                ]);
            }

            return $invoice;
        });
    }

    protected function payIfPaid(Request $request, PurchaseInvoice $invoice, AccountingService $accounting): void
    {
        if ($request->input('payment_status') !== 'paid') {
            return;
        }
        $invoice->refresh();
        $amount = min((float) $request->input('paid_amount', 0), (float) $invoice->total);
        if ($amount <= 0) {
            return;
        }
        if (! $request->filled('treasury_id')) {
            throw new RuntimeException('اختر الخزينة لصرف المبلغ.');
        }
        $accounting->recordPayment([
            'treasury_id' => $request->input('treasury_id'),
            'amount' => $amount,
            'transacted_at' => $invoice->invoice_date?->toDateString() ?? now()->toDateString(),
            'party_type' => Supplier::class,
            'party_id' => $invoice->supplier_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
            'notes' => 'صرف فاتورة مشتريات '.$invoice->number,
        ]);
    }
}
