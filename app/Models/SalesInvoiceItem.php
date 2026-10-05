<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'product_id', 'description', 'qty',
        'unit_price', 'discount', 'tax', 'total',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
