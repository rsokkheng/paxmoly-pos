<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now      = now();
        $products = DB::table('products')->get();

        foreach ($products as $p) {
            // Resolve pack size from packing string
            $packSize = 1;
            if (!empty($p->packing)) {
                if (preg_match('/(\d+)\s*(?:pcs?|ctns?|cans?|packs?|cases?|box(?:es)?|btls?|pkts?)/i', $p->packing, $m)) {
                    $packSize = max(1, (int) $m[1]);
                } elseif (preg_match('/(\d+)/', $p->packing, $m)) {
                    $packSize = max(1, (int) $m[1]);
                }
            }
            $uomCase = max(1, (int) ($p->uom_case ?? 1));
            if ($uomCase <= 1 && $packSize > 1) {
                $uomCase = $packSize;
            }

            // PCS unit
            DB::table('product_units')->insertOrIgnore([
                'product_id'    => $p->id,
                'unit_type'     => 'piece',
                'label'         => 'PCS',
                'uom'           => 1,
                'barcode'       => $p->barcode ?? null,
                'buying_price'  => $p->buying_price  ?? 0,
                'selling_price' => $p->selling_price ?? 0,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // CASE unit — only when pack size is meaningful
            $hasCasePrice = (($p->selling_price_case ?? 0) > 0 || ($p->buying_price_case ?? 0) > 0);
            if ($uomCase > 1 || $hasCasePrice) {
                $sellingCase = ($p->selling_price_case ?? 0) > 0
                    ? $p->selling_price_case
                    : (($p->selling_price ?? 0) * $uomCase);
                $buyingCase  = ($p->buying_price_case ?? 0) > 0
                    ? $p->buying_price_case
                    : (($p->buying_price ?? 0) * $uomCase);

                DB::table('product_units')->insertOrIgnore([
                    'product_id'    => $p->id,
                    'unit_type'     => 'carton',
                    'label'         => 'CASE',
                    'uom'           => $uomCase,
                    'barcode'       => null,
                    'buying_price'  => $buyingCase,
                    'selling_price' => $sellingCase,
                    'is_active'     => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('product_units')->delete();
    }
};
