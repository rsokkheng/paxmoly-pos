<?php
namespace App\Models;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─── app/Models/Supplier.php ──────────────────────────────────────
class Supplier extends Model {
    use SoftDeletes;
    protected $fillable = [
        'name','company','email','phone','address','city','country','balance','is_active','notes'
    ];
    protected $casts = ['is_active' => 'boolean', 'balance' => 'decimal:2'];
    public function purchases() { return $this->hasMany(Purchase::class); }
}