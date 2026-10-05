<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesInvoice;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('admin.pos.index', [
            'categories' => ProductCategory::query()->orderBy('name')->get(),
            'customers' => Customer::query()->where('status', 'active')->orderBy('name')->get(),
            'treasuries' => Treasury::query()->where('is_active', true)->orderBy('name')->get(),
            'posProducts' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->sale_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => (float) $product->current_stock,
                'type' => $product->type,
                'category_id' => $product->category_id,
                'letter' => mb_substr($product->name, 0, 1),
            ]),
        ]);
    }

    public function checkout(Request $request, AccountingService $accounting)
    {
        $types = ['dine_in' => 'صالة', 'takeaway' => 'تيك أواي', 'delivery' => 'دليفري'];
        try {
            $data = $request->validate([
                'customer_id' => ['required', 'exists:customers,id'],
                'payment_status' => ['required', 'in:paid,unpaid'],
                'paid_amount' => ['nullable', 'numeric', 'min:0'],
                'treasury_id' => ['nullable', 'exists:treasuries,id'],
                'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'exists:products,id'],
                'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            ]);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        }

        try {
            $invoice = DB::transaction(function () use ($data, $types, $accounting) {
                $products = Product::query()
                    ->whereIn('id', collect($data['items'])->pluck('product_id'))
                    ->get()
                    ->keyBy('id');

                $subtotal = $tax = 0;
                $lines = [];
                foreach ($data['items'] as $row) {
                    $product = $products[$row['product_id']] ?? null;
                    if (! $product || ! $product->is_active) {
                        throw new RuntimeException('أحد المنتجات غير متاح للبيع.');
                    }
                    $qty = (float) $row['qty'];
                    $price = (float) $product->sale_price;
                    $line = $qty * $price;
                    $lineTax = round($line * ((float) $product->tax_rate / 100), 2);
                    $subtotal += $line;
                    $tax += $lineTax;
                    $lines[] = [
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'unit_price' => $price,
                        'discount' => 0,
                        'tax' => $lineTax,
                        'total' => $line + $lineTax,
                    ];
                }

                $headerDiscount = (float) ($data['discount'] ?? 0);
                if ($headerDiscount > $subtotal + $tax) {
                    throw new RuntimeException('قيمة الخصم أكبر من إجمالي الطلب.');
                }
                $total = max($subtotal + $tax - $headerDiscount, 0);

                $invoice = SalesInvoice::query()->create([
                    'number' => CompanySetting::current()->nextNumber('sale'),
                    'customer_id' => $data['customer_id'],
                    'invoice_date' => now()->toDateString(),
                    'status' => 'draft',
                    'subtotal' => $subtotal,
                    'discount' => $headerDiscount,
                    'tax' => $tax,
                    'total' => $total,
                    'paid' => 0,
                    'remaining' => $total,
                    'notes' => 'نقطة بيع — '.$types[$data['order_type']],
                    'created_by' => auth()->id(),
                ]);
                foreach ($lines as $line) {
                    $invoice->items()->create($line);
                }

                $accounting->confirmSalesInvoice($invoice->fresh('items.product'));

                $paidAmount = $data['payment_status'] === 'unpaid'
                    ? 0.0
                    : min((float) ($data['paid_amount'] ?? 0), $total);

                if ($paidAmount > 0) {
                    if (empty($data['treasury_id'])) {
                        throw new RuntimeException('اختر الخزينة لتحصيل المبلغ.');
                    }
                    $accounting->recordReceipt([
                        'treasury_id' => $data['treasury_id'],
                        'amount' => $paidAmount,
                        'transacted_at' => now()->toDateString(),
                        'party_type' => Customer::class,
                        'party_id' => $invoice->customer_id,
                        'invoice_id' => $invoice->id,
                        'method' => 'cash',
                        'notes' => 'تحصيل نقطة البيع '.$invoice->number,
                    ]);
                }

                return $invoice->fresh();
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $message = match ($invoice->status) {
            'paid' => 'تم تأكيد وتحصيل الطلب '.$invoice->number.'.',
            'partial' => 'تم تأكيد الطلب '.$invoice->number.' وتحصيل جزء من المبلغ.',
            default => 'تم تأكيد الطلب '.$invoice->number.' كغير مدفوع.',
        };

        return redirect()->route('pos.index')->with('success', $message);
    }
}
