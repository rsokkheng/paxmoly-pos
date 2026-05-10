<?php
namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'short_name'];
    public function products() { return $this->hasMany(Product::class); }
}