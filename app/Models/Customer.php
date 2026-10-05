<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    protected $fillable = [
        'code', 'name', 'phone', 'email', 'address', 'tax_number',
        'opening_balance', 'current_balance', 'credit_limit', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(MoneyTransaction::class, 'party');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
