<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('role')->latest()->paginate(15),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->load('extraPermissions'),
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,disabled,suspended'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['two_factor_enabled'] = $request->boolean('two_factor_enabled', true);
        $data['email_verified_at'] = now();
        $user = User::query()->create($data);
        ActivityLog::record('users', 'create', "إضافة مستخدم {$user->name}", $user);

        return back()->with('success', 'تم إضافة المستخدم.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,disabled,suspended'],
        ]);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['two_factor_enabled'] = $request->boolean('two_factor_enabled');
        $user->update($data);
        $user->extraPermissions()->sync($request->input('permissions', []));
        ActivityLog::record('users', 'update', "تعديل مستخدم {$user->name}", $user);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم.');
    }
}
