<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id', 'type', 'qty', 'unit_cost', 'reference_type', 'reference_id',
        'moved_at', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'moved_at' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
