<?php
namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─── app/Models/StockMovement.php ─────────────────────────────────
class StockMovement extends Model {
    protected $fillable = [
        'product_id','user_id','type','quantity',
        'before_quantity','after_quantity','reference','notes'
    ];
    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(\App\Models\User::class); }
}