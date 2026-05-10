<?php
namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─── app/Models/Tax.php ───────────────────────────────────────────
class Tax extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'rate', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
    public function products() { return $this->hasMany(Product::class); }
}