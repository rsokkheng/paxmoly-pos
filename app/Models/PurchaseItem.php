<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_cost',       // actual buying cost per unit
        'unit_price',      // legacy / selling reference price
        'tax_amount',
        'discount_amount',
        'subtotal',        // (quantity × unit_cost) - discount + tax
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