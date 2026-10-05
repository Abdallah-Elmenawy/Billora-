<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesInvoice::with('customer', 'creator')
            ->when($request->q, fn ($q) => $q->where('number', 'like', '%'.$request->q.'%'))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status));

        return view('admin.sales.index', [
            'invoices' => (clone $query)->latest()->paginate(15)->withQueryString(),
            'displayedTotal' => (clone $query)->sum('total'),
            'grandTotal' => SalesInvoice::query()->where('status', '!=', 'cancelled')->sum('total'),
            'customers' => Customer::query()->orderBy('name')->get(),
            'formCustomers' => Customer::query()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'invoice' => new SalesInvoice(['invoice_date' => now()->toDateString()]),
            'treasuries' => Treasury::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return redirect()->route('sales.index');
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $invoice = $this->saveInvoice($request);
        if ($request->boolean('confirm')) {
            try {
                DB::transaction(function () use ($request, $invoice, $accounting) {
                    $accounting->confirmSalesInvoice($invoice->fresh('items.product'));
                    $this->collectIfPaid($request, $invoice, $accounting);
                });
            } catch (RuntimeException $e) {
                return redirect()->route('sales.edit', $invoice)->with('error', $e->getMessage());
            }
        }

        return redirect()->route('sales.show', $invoice->fresh())->with('success', 'تم حفظ فاتورة المبيعات.');
    }

    public function show(SalesInvoice $sale)
    {
        $sale->load(['customer', 'items.product', 'payments.treasury', 'creator']);

        return view('admin.sales.show', ['invoice' => $sale, 'treasuries' => Treasury::query()->where('is_active', true)->get()]);
    }

    public function edit(SalesInvoice $sale)
    {
        if (! $sale->isEditable()) {
            return redirect()->route('sales.show', $sale)->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }

        return view('admin.sales.form', [
            'invoice' => $sale->load('items'),
            'customers' => Customer::query()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SalesInvoice $sale, AccountingService $accounting)
    {
        if (! $sale->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل فاتورة مؤكدة.');
        }
        $invoice = $this->saveInvoice($request, $sale);
        if ($request->boolean('confirm')) {
            try {
                DB::transaction(function () use ($request, $invoice, $accounting) {
                    $accounting->confirmSalesInvoice($invoice->fresh('items.product'));
                    $this->collectIfPaid($request, $invoice, $accounting);
                });
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return redirect()->route('sales.show', $invoice->fresh())->with('success', 'تم تحديث الفاتورة.');
    }

    public function confirm(SalesInvoice $sale, AccountingService $accounting)
    {
        try {
            $accounting->confirmSalesInvoice($sale->load('items.product'));
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تأكيد الفاتورة.');
    }

    public function pay(Request $request, SalesInvoice $sale, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
        ]);
        if (! in_array($sale->status, ['confirmed', 'partial'], true) || $data['amount'] > (float) $sale->remaining) {
            return back()->with('error', 'لا يمكن التحصيل بهذا المبلغ.');
        }
        try {
            $accounting->recordReceipt($data + [
                'party_type' => Customer::class, 'party_id' => $sale->customer_id,
                'invoice_id' => $sale->id, 'method' => 'cash',
            ]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    public function print(SalesInvoice $sale)
    {
        return view('admin.sales.print', ['invoice' => $sale->load(['customer', 'items.product'])]);
    }

    public function cancel(SalesInvoice $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'يمكن إلغاء المسودة فقط.');
        }
        $sale->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء الفاتورة.');
    }

    public function destroy(SalesInvoice $sale)
    {
        if ($sale->status !== 'draft') {
            return back()->with('error', 'يمكن حذف المسودة فقط.');
        }
        $sale->delete();

        return back()->with('success', 'تم حذف الفاتورة.');
    }

    protected function saveInvoice(Request $request, ?SalesInvoice $invoice = null): SalesInvoice
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data, $invoice) {
            $subtotal = $tax = $itemDiscount = 0;
            foreach ($data['items'] as $item) {
                $line = ((float) $item['qty'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0);
                $subtotal += $line;
                $tax += (float) ($item['tax'] ?? 0);
                $itemDiscount += (float) ($item['discount'] ?? 0);
            }
            $headerDiscount = (float) ($data['discount'] ?? 0);
            $total = max($subtotal + $tax - $headerDiscount, 0);
            $payload = [
                'customer_id' => $data['customer_id'], 'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null, 'status' => 'draft',
                'subtotal' => $subtotal + $itemDiscount, 'discount' => $headerDiscount + $itemDiscount,
                'tax' => $tax, 'total' => $total, 'paid' => 0, 'remaining' => $total,
                'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
            ];
            if (! $invoice) {
                $payload['number'] = CompanySetting::current()->nextNumber('sale');
                $invoice = SalesInvoice::query()->create($payload);
            } else {
                $invoice->update($payload);
                $invoice->items()->delete();
            }
            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'], 'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'], 'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => ((float) $item['qty'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0) + (float) ($item['tax'] ?? 0),
                ]);
            }

            return $invoice;
        });
    }

    protected function collectIfPaid(Request $request, SalesInvoice $invoice, AccountingService $accounting): void
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
            throw new RuntimeException('اختر الخزينة لتحصيل المبلغ.');
        }
        $accounting->recordReceipt([
            'treasury_id' => $request->input('treasury_id'),
            'amount' => $amount,
            'transacted_at' => $invoice->invoice_date?->toDateString() ?? now()->toDateString(),
            'party_type' => Customer::class,
            'party_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
            'notes' => 'تحصيل فاتورة مبيعات '.$invoice->number,
        ]);
    }
}
