<?php
namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->slug = Str::slug($m->name));
    }
    public function products() { return $this->hasMany(Product::class); }
}