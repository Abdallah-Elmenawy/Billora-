<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $user->update($request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]));

        return back()->with('success', 'تم تحديث الملف الشخصي.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        auth()->user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.edit', ['tab' => 'password'])->with('success', 'تم تغيير كلمة المرور.');
    }
}
