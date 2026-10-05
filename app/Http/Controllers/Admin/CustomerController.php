<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Treasury;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with('creator')
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('code', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function accounts()
    {
        $customers = Customer::query()
            ->withSum(['invoices as sales_total' => fn ($q) => $q->whereIn('status', ['confirmed', 'partial', 'paid'])], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', ['confirmed', 'partial', 'paid'])], 'paid')
            ->orderByDesc('current_balance')->paginate(20);

        return view('admin.customers.accounts', compact('customers'));
    }

    public function create()
    {
        return redirect()->route('customers.index');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = $data['code'] ?? 'CUS-'.str_pad((string) (Customer::query()->max('id') + 1), 4, '0', STR_PAD_LEFT);
        $data['created_by'] = auth()->id();
        $data['current_balance'] = $data['opening_balance'] ?? 0;
        $customer = Customer::query()->create($data);
        ActivityLog::record('customers', 'create', "إضافة عميل {$customer->name}", $customer);

        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['invoices' => fn ($q) => $q->latest(), 'payments' => fn ($q) => $q->latest()]);

        return view('admin.customers.show', [
            'customer' => $customer,
            'treasuries' => Treasury::query()->where('is_active', true)->get(),
        ]);
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer->id));
        ActivityLog::record('customers', 'update', "تعديل عميل {$customer->name}", $customer);

        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'تم حذف العميل.');
    }

    public function collect(Request $request, Customer $customer, AccountingService $accounting)
    {
        $data = $request->validate([
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transacted_at' => ['required', 'date'],
            'method' => ['required', 'in:cash,bank,transfer'],
        ]);
        try {
            $accounting->recordReceipt($data + ['party_type' => Customer::class, 'party_id' => $customer->id]);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:customers,code,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric'],
            'credit_limit' => ['nullable', 'numeric'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
