<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit_type', 20);           // 'piece' | 'carton'
            $table->string('label', 20)->default('PCS');
            $table->unsignedSmallInteger('uom')->default(1); // pieces per this selling unit
            $table->string('barcode', 100)->nullable();
            $table->decimal('buying_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
