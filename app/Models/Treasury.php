<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treasury extends Model
{
    protected $fillable = [
        'name', 'type', 'account_number', 'account_id',
        'opening_balance', 'current_balance', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
