<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('uom_case');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('uom_pcs', 10, 2)->default(1)->after('selling_price_case');
            $table->decimal('uom_case', 10, 2)->default(1)->after('uom_pcs');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['uom_pcs', 'uom_case']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('uom_case', 50)->nullable()->default('CTN');
        });
    }
};
