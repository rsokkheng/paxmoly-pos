<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'unit_type',
        'label',
        'uom',
        'barcode',
        'buying_price',
        'selling_price',
        'is_active',
    ];

    protected $casts = [
        'buying_price'  => 'decimal:2',
        'selling_price' => 'decimal:2',
        'uom'           => 'integer',
        'is_active'     => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
