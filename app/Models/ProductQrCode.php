<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQrCode extends Model
{
    protected $fillable = [
        'product_id',
        'product_unit_id',
        'unit_type',
        'content_type',
        'qr_content',
        'label_title',
        'label_subtitle',
        'label_barcode',
        'default_copies',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'default_copies' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Build a QR record from a ProductUnit (or null for product-level SKU).
     */
    public static function buildFromUnit(Product $product, ?ProductUnit $unit, string $contentType = 'barcode'): self
    {
        $unitType = $unit?->unit_type;
        $price    = $unit?->selling_price ?? $product->selling_price;
        $label    = $unit?->label ?? 'ITEM';
        $barcode  = $unit?->barcode ?: $product->code;

        $content = match ($contentType) {
            'sku'  => $product->code,
            'json' => json_encode([
                'id' => $product->id,
                'ut' => $unitType,
                'p'  => round((float) $price, 2),
            ]),
            default => $barcode ?: $product->code,
        };

        return new self([
            'product_id'      => $product->id,
            'product_unit_id' => $unit?->id,
            'unit_type'       => $unitType,
            'content_type'    => $contentType,
            'qr_content'      => $content,
            'label_title'     => $product->name_en,
            'label_subtitle'  => '$' . number_format((float) $price, 2) . ' / ' . $label,
            'label_barcode'   => $barcode,
            'default_copies'  => 1,
            'created_by'      => auth()->id(),
        ]);
    }
}
