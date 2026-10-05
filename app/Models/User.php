<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'status', 'role_id',
        'two_factor_enabled', 'code', 'expires_at', 'otp_attempts',
        'otp_locked_until', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token', 'code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'expires_at' => 'datetime',
            'otp_locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function extraPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->role?->permissions()->where('slug', $slug)->exists()) {
            return true;
        }

        return $this->extraPermissions()->where('slug', $slug)->exists();
    }

    public function generateCode(): void
    {
        $this->timestamps = false;
        $this->code = (string) random_int(1000, 9999);
        $this->expires_at = now()->addSeconds(60);
        $this->otp_attempts = 0;
        $this->save();
    }

    public function resetCode(): void
    {
        $this->timestamps = false;
        $this->code = null;
        $this->expires_at = null;
        $this->otp_attempts = 0;
        $this->otp_locked_until = null;
        $this->save();
    }

    public function isOtpLocked(): bool
    {
        return $this->otp_locked_until && $this->otp_locked_until->isFuture();
    }
}
