<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'selling_unit',
        'pack_size',
        'unit_cost',
        'unit_price',
        'tax_amount',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_cost'       => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function product()  { return $this->belongsTo(Product::class)->withTrashed(); }
}