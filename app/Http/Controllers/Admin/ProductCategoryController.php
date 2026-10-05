<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => ProductCategory::query()->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        ProductCategory::query()->create($request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]));

        return back()->with('success', 'تم إضافة التصنيف.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $category->update($request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]));

        return back()->with('success', 'تم تحديث التصنيف.');
    }

    public function destroy(ProductCategory $category)
    {
        $category->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }
}
