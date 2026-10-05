<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'category_id', 'type', 'unit', 'cost_price', 'sale_price',
        'tax_rate', 'min_stock', 'current_stock', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'min_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function isLinked(): bool
    {
        return $this->movements()->exists()
            || $this->salesItems()->exists()
            || $this->purchaseItems()->exists()
            || $this->salesReturnItems()->exists()
            || $this->purchaseReturnItems()->exists();
    }

    public function isLowStock(): bool
    {
        return $this->type === 'product' && $this->current_stock <= $this->min_stock;
    }
}
