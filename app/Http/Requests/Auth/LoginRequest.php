<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Notifications\TwoFoctorCode;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $login = trim((string) $this->input('email'));
        $user = User::query()->where('email', $login)->orWhere('username', $login)->first();

        if (! $user || ! Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }
        if ($user->status === 'disabled') {
            throw ValidationException::withMessages(['email' => 'حسابك معطّل، تواصل مع الإدارة.']);
        }
        if ($user->status === 'suspended') {
            throw ValidationException::withMessages(['email' => 'حسابك موقوف، تواصل مع الإدارة.']);
        }

        Auth::login($user, $this->boolean('remember'));
        if ($user->two_factor_enabled) {
            $user->generateCode();
            $user->notify(new TwoFoctorCode);
        } else {
            $user->resetCode();
            $user->forceFill(['last_login_at' => now()])->save();
        }
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }
        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
