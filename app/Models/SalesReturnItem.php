<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    protected $fillable = ['sales_return_id', 'product_id', 'qty', 'unit_price', 'total'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:3', 'unit_price' => 'decimal:2', 'total' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
