<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::with('creator')
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('code', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function accounts()
    {
        $suppliers = Supplier::query()
            ->withSum(['invoices as purchases_total' => fn ($q) => $q->whereIn('status', ['confirmed', 'partial', 'paid'])], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', ['confirmed', 'partial', 'paid'])], 'paid')
            ->orderByDesc('current_balance')->paginate(20);

        return view('admin.suppliers.accounts', compact('suppliers'));
    }

    public function create()
    {
        return redirect()->route('suppliers.index');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = $data['code'] ?? 'SUP-'.str_pad((string) (Supplier::query()->max('id') + 1), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['current_balance'] = $data['opening_balance'] ?? 0;
        $supplier = Supplier::query()->create($data);
        ActivityLog::record('suppliers', 'create', "إضافة مورد {$supplier->name}", $supplier);

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['invoices' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest()]);

        return view('admin.suppliers.show', [
            'supplier' => $supplier,
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request, $supplier->id));
        ActivityLog::record('suppliers', 'update', "تعديل مورد {$supplier->name}", $supplier);

        return redirect()->route('suppliers.index')->with('success', 'تم تحديث بيانات المورد.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد.');
    }

    public function pay(Request $request, Supplier $supplier, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank,transfer'],
        ]);
        try {
            $accounting->recordPayment($data + ['party_type' => Supplier::class, 'party_id' => $supplier->id]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل الدفعة.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:suppliers,code,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
