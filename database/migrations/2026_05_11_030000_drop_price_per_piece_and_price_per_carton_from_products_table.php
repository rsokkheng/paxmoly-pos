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
            if (Schema::hasColumn('products', 'price_per_piece')) {
                $table->dropColumn('price_per_piece');
            }
            if (Schema::hasColumn('products', 'price_per_carton')) {
                $table->dropColumn('price_per_carton');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_per_piece', 12, 2)->default(0.00);
            $table->decimal('price_per_carton', 12, 2)->default(0.00);
        });
    }
};
