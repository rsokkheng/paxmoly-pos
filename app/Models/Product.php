<?php
namespace App\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductUnit;
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
        'brand_id',
        'unit_id',
        'tax_id',
        'name',
        'brand_name',
        'code',           // unique product code (used in controller as 'code', not 'sku')
        'barcode',
        'description',
        'image',
        'buying_price',
        'selling_price',
        'buying_price_case',
        'selling_price_case',
        'uom_pcs',
        'uom_case',
        'stock_quantity',
        'alert_quantity', // low-stock threshold
        'is_active',
        'packing',
    ];

    protected $casts = [
        'buying_price'      => 'decimal:2',
        'selling_price'     => 'decimal:2',
        'buying_price_case'  => 'decimal:2',
        'selling_price_case' => 'decimal:2',
        'uom_pcs'            => 'decimal:2',
        'uom_case'           => 'decimal:2',
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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
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

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function pcsUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('unit_type', 'piece');
    }

    public function caseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('unit_type', 'carton');
    }

    public function qrCodes()
    {
        return $this->hasMany(ProductQrCode::class);
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

    /**
     * Parse the pack size from a packing string like "330ML X 24PCS", "24 CAN/CTN", "20 PCS/PACK".
     * Prefers a number immediately followed by a count keyword (PCS, CTN, CAN, PACK …).
     * Falls back to the first number found in the string.
     */
    public static function parsePackingSize(?string $packing): int
    {
        if (empty($packing)) return 1;
        // Number + count unit: "24PCS", "24 CTN", "12 PACK", etc.
        if (preg_match('/(\d+)\s*(?:pcs?|ctns?|cans?|packs?|cases?|box(?:es)?|btls?|pkts?)/i', $packing, $m)) {
            return max(1, (int) $m[1]);
        }
        // Fallback: first number in the string
        if (preg_match('/(\d+)/', $packing, $m)) {
            return max(1, (int) $m[1]);
        }
        return 1;
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
