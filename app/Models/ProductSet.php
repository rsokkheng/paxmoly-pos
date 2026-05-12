<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSet extends Model
{
    protected $fillable = [
        'name', 'code', 'description', 'image',
        'selling_price', 'is_active', 'created_by',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function items()
    {
        return $this->hasMany(ProductSetItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Computed helpers ──────────────────────────────────────────────

    /**
     * Maximum sellable sets = minimum across all components of
     * floor(component_stock / quantity_needed_per_set).
     */
    public function availableQty(): int
    {
        if ($this->items->isEmpty()) return 0;

        $min = PHP_INT_MAX;
        foreach ($this->items as $item) {
            $stock   = (int) $item->product->stock_quantity;
            $unitQty = $item->unit_type === 'carton'
                ? $item->quantity * ($item->productUnit?->uom ?? 1)
                : $item->quantity;
            $avail = $unitQty > 0 ? (int) floor($stock / $unitQty) : 0;
            $min   = min($min, $avail);
        }

        return $min === PHP_INT_MAX ? 0 : $min;
    }

    /**
     * Total cost = sum of component buying prices × quantities.
     */
    public function totalCost(): float
    {
        return (float) $this->items->sum(function ($item) {
            $price = $item->unit_type === 'carton'
                ? ($item->productUnit?->buying_price ?? $item->product->buying_price)
                : ($item->productUnit?->buying_price ?? $item->product->buying_price);
            return $price * $item->quantity;
        });
    }
}
