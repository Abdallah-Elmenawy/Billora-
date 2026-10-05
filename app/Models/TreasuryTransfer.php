<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryTransfer extends Model
{
    protected $fillable = [
        'number', 'from_treasury_id', 'to_treasury_id', 'amount', 'transferred_at',
        'notes', 'journal_id', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'transferred_at' => 'date'];
    }

    public function fromTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'from_treasury_id');
    }

    public function toTreasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class, 'to_treasury_id');
    }
}
