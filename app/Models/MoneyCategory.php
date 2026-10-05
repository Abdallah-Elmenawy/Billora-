<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MoneyCategory extends Model
{
    protected $fillable = ['name', 'type', 'account_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MoneyEntry::class);
    }
}
