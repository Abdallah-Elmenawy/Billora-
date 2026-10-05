<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('sku', 'like', '%'.$request->q.'%'))
            ->latest()->paginate(15)->withQueryString();
        $categories = ProductCategory::query()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return redirect()->route('products.index');
    }

    public function store(Request $request)
    {
        $product = Product::query()->create($this->validated($request));
        ActivityLog::record('products', 'create', "إضافة منتج {$product->name}", $product);

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => ProductCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product->id));
        ActivityLog::record('products', 'update', "تعديل منتج {$product->name}", $product);

        return redirect()->route('products.index')->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product)
    {
        if ($product->isLinked()) {
            return back()->with('error', 'لا يمكن حذف المنتج لأنه مرتبط بفواتير أو حركات مخزون.');
        }

        try {
            $name = $product->name;
            $product->delete();
            ActivityLog::record('products', 'delete', "حذف منتج {$name}");
        } catch (QueryException $e) {
            return back()->with('error', 'لا يمكن حذف المنتج لأنه مرتبط ببيانات أخرى في النظام.');
        }

        return redirect()->route('products.index')->with('success', 'تم حذف المنتج.');
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,'.($id ?? 'NULL')],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'type' => ['required', 'in:product,service'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['nullable', 'numeric'],
            'sale_price' => ['required', 'numeric'],
            'min_stock' => ['nullable', 'numeric'],
            'current_stock' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
