<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price_per_piece', 12, 2)->default(0.00);
            $table->decimal('buying_price_per_carton', 12, 2)->default(0.00);
            $table->decimal('selling_price_per_piece', 12, 2)->default(0.00);
            $table->decimal('selling_price_per_carton', 12, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'buying_price_per_piece',
                'buying_price_per_carton',
                'selling_price_per_piece',
                'selling_price_per_carton',
            ]);
        });
    }
};
