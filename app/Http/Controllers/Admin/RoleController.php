<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->with('permissions')->latest()->get(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
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
        $role->permissions()->sync($request->input('permissions', []));
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
}
