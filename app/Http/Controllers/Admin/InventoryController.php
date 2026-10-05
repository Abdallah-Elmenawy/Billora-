<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class InventoryController extends Controller
{
    public function index()
    {
        return view('admin.inventory.index', [
            'products' => Product::with('category')->where('type', 'product')->orderBy('name')->paginate(20),
            'formProducts' => Product::query()->where('type', 'product')->where('is_active', true)->orderBy('name')->get(),
            'movements' => InventoryMovement::with(['product', 'creator'])->latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,adjust'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);
        try {
            $accounting->adjustStock(Product::query()->findOrFail($data['product_id']), (float) $data['qty'], $data['type'], $data['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسجيل حركة المخزون.');
    }
}
