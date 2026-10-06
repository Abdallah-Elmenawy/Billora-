<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        PermissionCatalog::sync();

        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->with('permissions')->latest()->get(),
            'permissions' => $this->groupedPermissions(),
            'modules' => PermissionCatalog::modules(),
        ]);
    }

    public function edit(Role $role)
    {
        PermissionCatalog::sync();

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->groupedPermissions(),
            'modules' => PermissionCatalog::modules(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);
        $base = Str::slug($data['name']) ?: 'role';
        $slug = $base;
        $i = 1;
        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        $role = Role::query()->create($data + ['slug' => $slug]);
        $role->permissions()->sync($request->input('permissions', []));
        ActivityLog::record('users', 'create', "إضافة دور {$role->name}", $role);

        return back()->with('success', 'تم إنشاء الدور. يمكنك الآن إنشاء مستخدم بهذا الدور.');
    }

    public function update(Request $request, Role $role)
    {
        $role->update($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]));
        if ($role->slug === 'admin') {
            $role->permissions()->sync(Permission::query()->pluck('id'));
        } else {
            $role->permissions()->sync($request->input('permissions', []));
        }
        ActivityLog::record('users', 'update', "تعديل دور {$role->name}", $role);

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->with('error', 'لا يمكن حذف دور مدير النظام.');
        }
        if ($role->users()->exists()) {
            return back()->with('error', 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين.');
        }
        $name = $role->name;
        $role->permissions()->detach();
        $role->delete();
        ActivityLog::record('users', 'delete', "حذف دور {$name}");

        return back()->with('success', 'تم حذف الدور.');
    }

    protected function groupedPermissions()
    {
        $grouped = Permission::query()->get()->groupBy('module');
        $order = ['view' => 1, 'create' => 2, 'update' => 3, 'delete' => 4];

        return collect(PermissionCatalog::modules())
            ->map(fn ($label, $module) => ($grouped->get($module) ?? collect())->sortBy(fn ($permission) => $order[$permission->action] ?? 9)->values())
            ->filter(fn ($items) => $items->isNotEmpty());
    }
}
