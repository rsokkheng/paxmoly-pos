<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─── app/Models/Discount.php ──────────────────────────────────────
class Discount extends Model {
    use SoftDeletes;
    protected $fillable = ['code','name','type','value','start_date','end_date','is_active'];
    protected $casts = [
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];
    public function isValid(): bool {
        $today = now()->toDateString();
        return $this->is_active
            && (!$this->start_date || $this->start_date <= $today)
            && (!$this->end_date   || $this->end_date   >= $today);
    }
}