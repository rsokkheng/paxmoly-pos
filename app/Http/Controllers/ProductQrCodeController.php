<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductQrCode;
use Illuminate\Http\Request;

class ProductQrCodeController extends Controller
{
    /** List all products with their QR status */
    public function index(Request $request)
    {
        $products = Product::with(['productUnits', 'qrCodes', 'brand', 'category'])
            ->where('is_active', true)
            ->when($request->search, fn($q) => $q->where('name_en', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->orderBy('name_en')
            ->paginate(30);

        return view('product-qr.index', compact('products'));
    }

    /**
     * Generate (or regenerate) QR codes for a product.
     * Creates one row per active unit type.
     */
    public function generate(Request $request, Product $product)
    {
        $contentType = $request->input('content_type', 'barcode');
        $product->load('productUnits');

        $activeUnits = $product->productUnits->where('is_active', true);

        foreach ($activeUnits as $unit) {
            $qr = ProductQrCode::buildFromUnit($product, $unit, $contentType);

            ProductQrCode::updateOrCreate(
                ['product_id' => $product->id, 'unit_type' => $unit->unit_type],
                $qr->getAttributes()
            );
        }

        // Also create a product-level (SKU) entry if no units exist
        if ($activeUnits->isEmpty()) {
            $qr = ProductQrCode::buildFromUnit($product, null, 'sku');
            ProductQrCode::updateOrCreate(
                ['product_id' => $product->id, 'unit_type' => null],
                $qr->getAttributes()
            );
        }

        return back()->with('success', 'QR codes generated for ' . $product->name_en);
    }

    /**
     * Printable label sheet for selected QR codes.
     * Accepts: ?qr_ids[]=1&qr_ids[]=2  or  ?product_id=5
     */
    public function print(Request $request)
    {
        $qrCodes = collect();

        if ($request->filled('qr_ids')) {
            $qrCodes = ProductQrCode::with('product')
                ->whereIn('id', $request->qr_ids)
                ->where('is_active', true)
                ->get();
        } elseif ($request->filled('product_id')) {
            $qrCodes = ProductQrCode::with('product')
                ->where('product_id', $request->product_id)
                ->where('is_active', true)
                ->get();
        }

        // Expand by copies
        $copies  = max(1, (int) $request->input('copies', 1));
        $labels  = $qrCodes->flatMap(fn($qr) => array_fill(0, $qr->default_copies * $copies, $qr));

        return view('product-qr.print', compact('labels'));
    }

    /** Toggle active state */
    public function toggle(ProductQrCode $qrCode)
    {
        $qrCode->update(['is_active' => !$qrCode->is_active]);
        return back()->with('success', 'QR code updated.');
    }

    /** Delete a QR code record */
    public function destroy(ProductQrCode $qrCode)
    {
        $qrCode->delete();
        return back()->with('success', 'QR code deleted.');
    }
}
