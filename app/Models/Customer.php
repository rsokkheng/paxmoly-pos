<?php
namespace App\Models;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─── app/Models/Customer.php ──────────────────────────────────────
class Customer extends Model {
    use SoftDeletes;
    protected $fillable = [
        'name','email','phone','address','city','country','balance','is_active','notes'
    ];
    protected $casts = ['is_active' => 'boolean', 'balance' => 'decimal:2'];
    public function sales() { return $this->hasMany(Sale::class); }
}