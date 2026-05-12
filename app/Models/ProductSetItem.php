<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSetItem extends Model
{
    protected $fillable = [
        'product_set_id', 'product_id', 'unit_type', 'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function productSet()
    {
        return $this->belongsTo(ProductSet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->hasOneThrough(
            ProductUnit::class,
            Product::class,
            'id',          // products.id
            'product_id',  // product_units.product_id
            'product_id',  // product_set_items.product_id
            'id'           // products.id
        )->where('product_units.unit_type', $this->unit_type ?? 'piece');
    }
}
