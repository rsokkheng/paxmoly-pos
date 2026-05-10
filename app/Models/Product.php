<?php
namespace App\Models;

use App\Models\Category;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'unit_id',
        'tax_id',
        'name',
        'code',           // unique product code (used in controller as 'code', not 'sku')
        'barcode',
        'description',
        'image',
        'buying_price',
        'selling_price',
        'stock_quantity',
        'alert_quantity', // low-stock threshold
        'is_active',
    ];

    protected $casts = [
        'buying_price'   => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_quantity' => 'integer',
        'is_active'      => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // ── Accessors ──────────────────────────────────────────────────
    public function getMarginAttribute(): float
    {
        return (float) $this->selling_price - (float) $this->buying_price;
    }

    public function getMarginPercentAttribute(): float
    {
        if ((float) $this->selling_price <= 0) return 0;
        return round($this->margin / (float) $this->selling_price * 100, 2);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_quantity;
    }

    // ── Scopes ───────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
