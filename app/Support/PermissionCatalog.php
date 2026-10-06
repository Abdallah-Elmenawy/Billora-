<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;

class PermissionCatalog
{
    public static function modules(): array
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'customers' => 'العملاء',
            'suppliers' => 'الموردون',
            'products' => 'المنتجات والتصنيفات',
            'inventory' => 'المخزون',
            'sales' => 'المبيعات ونقطة البيع',
            'purchases' => 'المشتريات',
            'accounting' => 'الحسابات',
            'treasury' => 'الخزينة',
            'expenses' => 'المصروفات والإيرادات',
            'reports' => 'التقارير',
            'users' => 'المستخدمون والصلاحيات',
            'settings' => 'الإعدادات',
        ];
    }

    public static function all(): array
    {
        return [
            ['name' => 'عرض لوحة التحكم', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view'],

            ['name' => 'عرض العملاء', 'slug' => 'customers.view', 'module' => 'customers', 'action' => 'view'],
            ['name' => 'إضافة عميل', 'slug' => 'customers.create', 'module' => 'customers', 'action' => 'create'],
            ['name' => 'تعديل عميل وتحصيل', 'slug' => 'customers.update', 'module' => 'customers', 'action' => 'update'],
            ['name' => 'حذف عميل', 'slug' => 'customers.delete', 'module' => 'customers', 'action' => 'delete'],

            ['name' => 'عرض الموردين', 'slug' => 'suppliers.view', 'module' => 'suppliers', 'action' => 'view'],
            ['name' => 'إضافة مورد', 'slug' => 'suppliers.create', 'module' => 'suppliers', 'action' => 'create'],
            ['name' => 'تعديل مورد وصرف', 'slug' => 'suppliers.update', 'module' => 'suppliers', 'action' => 'update'],
            ['name' => 'حذف مورد', 'slug' => 'suppliers.delete', 'module' => 'suppliers', 'action' => 'delete'],

            ['name' => 'عرض المنتجات والتصنيفات', 'slug' => 'products.view', 'module' => 'products', 'action' => 'view'],
            ['name' => 'إضافة منتج أو تصنيف', 'slug' => 'products.create', 'module' => 'products', 'action' => 'create'],
            ['name' => 'تعديل منتج أو تصنيف', 'slug' => 'products.update', 'module' => 'products', 'action' => 'update'],
            ['name' => 'حذف منتج أو تصنيف', 'slug' => 'products.delete', 'module' => 'products', 'action' => 'delete'],

            ['name' => 'عرض المخزون', 'slug' => 'inventory.view', 'module' => 'inventory', 'action' => 'view'],
            ['name' => 'تسجيل حركة مخزون', 'slug' => 'inventory.create', 'module' => 'inventory', 'action' => 'create'],

            ['name' => 'عرض المبيعات ونقطة البيع', 'slug' => 'sales.view', 'module' => 'sales', 'action' => 'view'],
            ['name' => 'إنشاء فاتورة أو طلب', 'slug' => 'sales.create', 'module' => 'sales', 'action' => 'create'],
            ['name' => 'تعديل وتأكيد وتحصيل', 'slug' => 'sales.update', 'module' => 'sales', 'action' => 'update'],
            ['name' => 'حذف فاتورة أو مرتجع', 'slug' => 'sales.delete', 'module' => 'sales', 'action' => 'delete'],

            ['name' => 'عرض المشتريات', 'slug' => 'purchases.view', 'module' => 'purchases', 'action' => 'view'],
            ['name' => 'إنشاء فاتورة مشتريات', 'slug' => 'purchases.create', 'module' => 'purchases', 'action' => 'create'],
            ['name' => 'تعديل وتأكيد وصرف', 'slug' => 'purchases.update', 'module' => 'purchases', 'action' => 'update'],
            ['name' => 'حذف فاتورة أو مرتجع', 'slug' => 'purchases.delete', 'module' => 'purchases', 'action' => 'delete'],

            ['name' => 'عرض الحسابات والقيود', 'slug' => 'accounting.view', 'module' => 'accounting', 'action' => 'view'],
            ['name' => 'إضافة حساب أو قيد', 'slug' => 'accounting.create', 'module' => 'accounting', 'action' => 'create'],
            ['name' => 'تعديل حساب', 'slug' => 'accounting.update', 'module' => 'accounting', 'action' => 'update'],

            ['name' => 'عرض الخزينة', 'slug' => 'treasury.view', 'module' => 'treasury', 'action' => 'view'],
            ['name' => 'تحصيل وصرف وتحويل', 'slug' => 'treasury.create', 'module' => 'treasury', 'action' => 'create'],

            ['name' => 'عرض المصروفات والإيرادات', 'slug' => 'expenses.view', 'module' => 'expenses', 'action' => 'view'],
            ['name' => 'إضافة عملية أو تصنيف', 'slug' => 'expenses.create', 'module' => 'expenses', 'action' => 'create'],
            ['name' => 'تعديل عملية', 'slug' => 'expenses.update', 'module' => 'expenses', 'action' => 'update'],
            ['name' => 'حذف عملية', 'slug' => 'expenses.delete', 'module' => 'expenses', 'action' => 'delete'],

            ['name' => 'عرض التقارير', 'slug' => 'reports.view', 'module' => 'reports', 'action' => 'view'],

            ['name' => 'عرض المستخدمين والأدوار والسجل', 'slug' => 'users.view', 'module' => 'users', 'action' => 'view'],
            ['name' => 'إضافة مستخدم أو دور', 'slug' => 'users.create', 'module' => 'users', 'action' => 'create'],
            ['name' => 'تعديل مستخدم أو دور', 'slug' => 'users.update', 'module' => 'users', 'action' => 'update'],
            ['name' => 'حذف دور', 'slug' => 'users.delete', 'module' => 'users', 'action' => 'delete'],

            ['name' => 'إدارة إعدادات النظام', 'slug' => 'settings.update', 'module' => 'settings', 'action' => 'update'],
        ];
    }

    public static function sync(): void
    {
        foreach (self::all() as $permission) {
            Permission::query()->updateOrCreate(['slug' => $permission['slug']], $permission);
        }

        $admin = Role::query()->where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::query()->pluck('id'));
        }

        $dashboardId = Permission::query()->where('slug', 'dashboard.view')->value('id');
        if ($dashboardId) {
            Role::query()->each(function (Role $role) use ($dashboardId) {
                $role->permissions()->syncWithoutDetaching([$dashboardId]);
            });
        }
    }
}
