<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Tracks which set produced this item (null = regular item)
            $table->foreignId('product_set_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('product_sets')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_set_id']);
            $table->dropColumn('product_set_id');
        });
    }
};
