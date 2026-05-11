<?php
namespace App\Models;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'unit_price', 'selling_unit',
        'discount_amount', 'discount_type', 'discount_value', 'tax_amount', 'subtotal',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function sale()    { return $this->belongsTo(Sale::class); }

    // withTrashed() ensures product name/code still appear in sale history
    // even after the product has been soft-deleted
    public function product() { return $this->belongsTo(Product::class)->withTrashed(); }
}