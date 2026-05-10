<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no',
        'supplier_id',
        'user_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'paid_amount',
        'status',        // enum: received | pending | cancelled
        'purchase_date',
        'notes',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'purchase_date'   => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($purchase) {
            if (empty($purchase->reference_no)) {
                $purchase->reference_no = 'PO-' . strtoupper(Str::random(4)) . '-' . now()->format('ymd');
            }
            if (empty($purchase->purchase_date)) {
                $purchase->purchase_date = now()->toDateString();
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function user()     { return $this->belongsTo(\App\Models\User::class); }
    public function items()    { return $this->hasMany(PurchaseItem::class); }

    // ── Accessors ─────────────────────────────────────────────────
    // due_amount is NOT a DB column — computed from grand_total - paid_amount
    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }

    public function getItemsCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->sum('quantity');
        }
        return (int) $this->items()->sum('quantity');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeReceived($query)  { return $query->where('status', 'received'); }
    public function scopePending($query)   { return $query->where('status', 'pending'); }
    public function scopeThisMonth($query) {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at',  now()->year);
    }
}