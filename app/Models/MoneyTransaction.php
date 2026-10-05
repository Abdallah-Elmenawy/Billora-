<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MoneyTransaction extends Model
{
    protected $fillable = [
        'number', 'type', 'treasury_id', 'party_type', 'party_id',
        'invoice_type', 'invoice_id', 'amount', 'transacted_at',
        'method', 'notes', 'journal_id', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'transacted_at' => 'date'];
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function party(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoice(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
