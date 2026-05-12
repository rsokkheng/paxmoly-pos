<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_set_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_set_id')->constrained('product_sets')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('unit_type', 20)->default('piece');  // 'piece' | 'carton'
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['product_set_id', 'product_id', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_set_items');
    }
};
