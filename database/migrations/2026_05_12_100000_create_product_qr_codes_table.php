<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_qr_codes', function (Blueprint $table) {
            $table->id();

            // Which product this QR belongs to
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Which selling unit (piece / carton) — null means generic (product-level)
            $table->foreignId('product_unit_id')
                  ->nullable()
                  ->constrained('product_units')
                  ->nullOnDelete();

            // 'piece' | 'carton' | null  (mirrors product_unit_id for quick filtering)
            $table->string('unit_type', 20)->nullable();

            // ── What is actually encoded in the QR ────────────────────────────
            // 'barcode' → uses the barcode field from product_units / products
            // 'sku'     → uses products.code (SKU)
            // 'json'    → compact JSON: {"id":1,"ut":"piece","p":21.20}
            $table->enum('content_type', ['barcode', 'sku', 'json'])->default('barcode');

            // The encoded string stored for reference / re-printing
            $table->string('qr_content', 500);

            // ── Label display fields (printed on the sticker) ─────────────────
            $table->string('label_title', 150)->nullable();    // product name
            $table->string('label_subtitle', 100)->nullable(); // e.g. "$21.20 / PCS"
            $table->string('label_barcode', 100)->nullable();  // human-readable barcode text

            // Default number of copies when sent to print
            $table->unsignedSmallInteger('default_copies')->default(1);

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // One QR row per product × unit_type combination
            $table->unique(['product_id', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_qr_codes');
    }
};
