<?php
namespace App\Models;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\SaleItem;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'customer_id', 'discount_id', 'tax_id', 'user_id',
        'subtotal', 'discount_amount', 'tax_amount', 'grand_total',
        'paid_amount', 'change_amount', 'payment_method', 'status', 'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'grand_total'     => 'decimal:2',   // ← the real total column
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    // ── Boot: auto-generate invoice number ────────────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($sale) {
            if (empty($sale->invoice_no)) {
                $sale->invoice_no = 'INV-' . strtoupper(Str::random(4)) . '-' . now()->format('ymd');
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────
    public function customer() { return $this->belongsTo(Customer::class); }
    public function discount() { return $this->belongsTo(Discount::class); }
    public function tax()      { return $this->belongsTo(Tax::class); }
    public function user()     { return $this->belongsTo(\App\Models\User::class); }
    public function items()    { return $this->hasMany(SaleItem::class); }

    // ── Accessors ─────────────────────────────────────────────────
    /**
     * Total units across all line items — used in views as $sale->items_count.
     * Avoids an extra query when items are already eager-loaded.
     */
    public function getItemsCountAttribute(): int
    {
        // Use loaded relation if available to avoid N+1
        if ($this->relationLoaded('items')) {
            return $this->items->sum('quantity');
        }
        return (int) $this->items()->sum('quantity');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
    public function scopeToday($query)     { return $query->whereDate('created_at', today()); }
    public function scopeThisMonth($query) {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at',  now()->year);
    }
}