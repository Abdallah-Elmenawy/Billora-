<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\MoneyCategory;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Treasury;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BilloraSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'عرض لوحة التحكم', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view'],
            ['name' => 'عرض العملاء', 'slug' => 'customers.view', 'module' => 'customers', 'action' => 'view'],
            ['name' => 'عرض الموردين', 'slug' => 'suppliers.view', 'module' => 'suppliers', 'action' => 'view'],
            ['name' => 'عرض المنتجات', 'slug' => 'products.view', 'module' => 'products', 'action' => 'view'],
            ['name' => 'عرض المخزون', 'slug' => 'inventory.view', 'module' => 'inventory', 'action' => 'view'],
            ['name' => 'عرض المبيعات', 'slug' => 'sales.view', 'module' => 'sales', 'action' => 'view'],
            ['name' => 'عرض المشتريات', 'slug' => 'purchases.view', 'module' => 'purchases', 'action' => 'view'],
            ['name' => 'عرض الحسابات', 'slug' => 'accounting.view', 'module' => 'accounting', 'action' => 'view'],
            ['name' => 'عرض الخزينة', 'slug' => 'treasury.view', 'module' => 'treasury', 'action' => 'view'],
            ['name' => 'عرض المصروفات', 'slug' => 'expenses.view', 'module' => 'expenses', 'action' => 'view'],
            ['name' => 'عرض التقارير', 'slug' => 'reports.view', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'عرض المستخدمين', 'slug' => 'users.view', 'module' => 'users', 'action' => 'view'],
            ['name' => 'إعدادات النظام', 'slug' => 'settings.update', 'module' => 'settings', 'action' => 'update'],
        ];
        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['slug' => $permission['slug']], $permission);
        }
        $adminRole = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'مدير النظام', 'description' => 'صلاحيات كاملة']);
        $adminRole->permissions()->sync(Permission::query()->pluck('id'));

        $admin = User::query()->updateOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'مدير Billora', 'username' => 'admin', 'password' => Hash::make('password'),
            'status' => 'active', 'role_id' => $adminRole->id, 'two_factor_enabled' => true, 'email_verified_at' => now(),
        ]);

        $accounts = [
            ['code' => '1000', 'name' => 'الأصول', 'type' => 'asset', 'parent' => null],
            ['code' => '1100', 'name' => 'الصندوق', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1200', 'name' => 'البنك', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1300', 'name' => 'العملاء', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '1400', 'name' => 'المخزون', 'type' => 'asset', 'parent' => '1000'],
            ['code' => '2000', 'name' => 'الالتزامات', 'type' => 'liability', 'parent' => null],
            ['code' => '2100', 'name' => 'الموردون', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '2200', 'name' => 'ضريبة المبيعات', 'type' => 'liability', 'parent' => '2000'],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => 'equity', 'parent' => null],
            ['code' => '3100', 'name' => 'رأس المال', 'type' => 'equity', 'parent' => '3000'],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => 'revenue', 'parent' => null],
            ['code' => '4100', 'name' => 'إيرادات المبيعات', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '4200', 'name' => 'إيرادات أخرى', 'type' => 'revenue', 'parent' => '4000'],
            ['code' => '5000', 'name' => 'المصروفات', 'type' => 'expense', 'parent' => null],
            ['code' => '5100', 'name' => 'تكلفة المبيعات', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '5200', 'name' => 'مصروفات تشغيلية', 'type' => 'expense', 'parent' => '5000'],
            ['code' => '5300', 'name' => 'خصم مسموح به', 'type' => 'expense', 'parent' => '5000'],
        ];
        $created = [];
        foreach ($accounts as $row) {
            $account = Account::query()->updateOrCreate(['code' => $row['code']], [
                'name' => $row['name'], 'type' => $row['type'],
                'parent_id' => $row['parent'] ? ($created[$row['parent']] ?? null) : null,
                'is_system' => true, 'is_active' => true,
            ]);
            $created[$row['code']] = $account->id;
        }
        Treasury::query()->updateOrCreate(['name' => 'الخزينة الرئيسية'], [
            'type' => 'cash', 'account_id' => $created['1100'], 'opening_balance' => 10000, 'current_balance' => 10000, 'is_active' => true,
        ]);
        Treasury::query()->updateOrCreate(['name' => 'الحساب البنكي'], [
            'type' => 'bank', 'account_id' => $created['1200'], 'opening_balance' => 0, 'current_balance' => 0, 'is_active' => true,
        ]);
        CompanySetting::query()->updateOrCreate(['id' => 1], [
            'name' => 'Billora', 'legal_name' => 'شركة بيلورا للمحاسبة', 'email' => 'info@billora.test',
            'phone' => '01000000000', 'address' => 'القاهرة، مصر', 'currency' => 'EGP', 'currency_symbol' => 'ج.م', 'language' => 'ar',
            'sale_prefix' => 'SAL', 'purchase_prefix' => 'PUR', 'payment_prefix' => 'PAY',
            'receipt_prefix' => 'REC', 'journal_prefix' => 'JRN',
            'sales_account_id' => $created['4100'], 'purchases_account_id' => $created['1400'],
            'inventory_account_id' => $created['1400'], 'cogs_account_id' => $created['5100'],
            'customers_account_id' => $created['1300'], 'suppliers_account_id' => $created['2100'],
            'tax_account_id' => $created['2200'], 'sales_discount_account_id' => $created['5300'],
        ]);
        MoneyCategory::query()->updateOrCreate(['name' => 'إيجار', 'type' => 'expense'], ['account_id' => $created['5200']]);
        MoneyCategory::query()->updateOrCreate(['name' => 'إيرادات متنوعة', 'type' => 'revenue'], ['account_id' => $created['4200']]);
        $cat = ProductCategory::query()->updateOrCreate(['name' => 'عام']);
        Product::query()->updateOrCreate(['sku' => 'P-001'], [
            'name' => 'منتج تجريبي', 'category_id' => $cat->id, 'type' => 'product', 'unit' => 'قطعة',
            'cost_price' => 50, 'sale_price' => 80, 'min_stock' => 5, 'current_stock' => 40, 'is_active' => true,
        ]);
        Customer::query()->updateOrCreate(['code' => 'CUS-0001'], [
            'name' => 'عميل تجريبي', 'phone' => '01111111111', 'status' => 'active', 'created_by' => $admin->id,
        ]);
        Supplier::query()->updateOrCreate(['code' => 'SUP-0001'], [
            'name' => 'مورد تجريبي', 'phone' => '01222222222', 'status' => 'active', 'created_by' => $admin->id,
        ]);
    }
}
