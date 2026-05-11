<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price_case', 12, 2)->default(0.00)->after('selling_price');
            $table->decimal('selling_price_case', 12, 2)->default(0.00)->after('buying_price_case');
            $table->string('uom_case', 50)->nullable()->default('CTN')->after('selling_price_case');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['buying_price_case', 'selling_price_case', 'uom_case']);
        });
    }
};
