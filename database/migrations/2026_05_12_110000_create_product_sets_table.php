<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 60)->unique();           // SKU for the set
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sets');
    }
};
