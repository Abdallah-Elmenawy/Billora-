<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'module', 'action', 'description',
        'auditable_type', 'auditable_id', 'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $module, string $action, string $description, $model = null): self
    {
        return static::query()->create([
            'user_id' => auth()->id(),
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'ip_address' => request()->ip(),
        ]);
    }
}
