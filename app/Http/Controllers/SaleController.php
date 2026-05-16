<?php 
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSet;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /** POS Screen */
    public function create() {
        $customers = \App\Models\Customer::where('is_active', true)->orderBy('name')->get();
        $discounts = \App\Models\Discount::where('is_active', true)->get();
        $taxes     = \App\Models\Tax::where('is_active', true)->get();
        $brands    = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $products  = Product::with(['brand', 'tax', 'productUnits' => fn($q) => $q->where('is_active', true)->orderBy('unit_type')])
                        ->where('is_active', true)
                        ->where('stock_quantity', '>', 0)
                        ->orderBy('name')
                        ->get();
        $productSets = ProductSet::with(['items.product.productUnits'])
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get();

        return view('sales.create', compact('customers', 'discounts', 'taxes', 'brands', 'products', 'productSets'));
    }

    /** Save the sale */
    public function store(Request $request) {
        $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'discount_id'      => 'nullable|exists:discounts,id',
            'tax_id'           => 'nullable|exists:taxes,id',
            'payment_method'   => 'required|in:cash,card,mobile,credit',
            'paid_amount'      => 'required|numeric|min:0',
            'items'            => 'required|array|min:1',
            'items.*.product_id'     => 'nullable|exists:products,id',
            'items.*.product_set_id' => 'nullable|exists:product_sets,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.selling_unit'   => 'nullable|in:piece,carton',
            'items.*.discount_type'  => 'nullable|string|in:pct,amt',
            'items.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Calculate totals
            $subtotal        = 0;
            $discountAmount  = 0;
            $taxAmount       = 0;

            $itemsData = [];

            foreach ($request->items as $item) {
                // ── Set item: expand into component products ──────────────────
                if (!empty($item['product_set_id'])) {
                    $set     = ProductSet::with('items.product.productUnits')->findOrFail($item['product_set_id']);
                    $setsQty = (int) $item['quantity'];
                    // Distribute the set price proportionally to components for accounting
                    $setPrice = round((float) $item['unit_price'], 2); // set price × qty

                    $lineTotal    = $setPrice * $setsQty;
                    $lineDisc     = $item['discount_amount'] ?? 0;
                    $lineTax      = $item['tax_amount']      ?? 0;

                    $subtotal       += $lineTotal;
                    $discountAmount += $lineDisc;
                    $taxAmount      += $lineTax;

                    $setDiscType  = $item['discount_type']  ?? null;
                    $setDiscValue = $item['discount_value'] ?? null;
                    $isFirstComp  = true;

                    foreach ($set->items as $setItem) {
                        $componentQty = $setItem->quantity * $setsQty;
                        $productUnit  = $setItem->product->productUnits->firstWhere('unit_type', $setItem->unit_type)
                                     ?? $setItem->product->productUnits->firstWhere('unit_type', 'piece');
                        $unitPrice    = round((float) ($productUnit->selling_price ?? 0), 2);

                        $itemsData[] = [
                            'product_id'      => $setItem->product_id,
                            'product_set_id'  => $set->id,
                            'quantity'        => $componentQty,
                            'unit_price'      => $unitPrice,
                            'selling_unit'    => $setItem->unit_type,
                            'discount_amount' => $isFirstComp ? $lineDisc : 0,
                            'discount_type'   => $isFirstComp ? $setDiscType  : null,
                            'discount_value'  => $isFirstComp ? $setDiscValue : null,
                            'tax_amount'      => 0,
                            'subtotal'        => $unitPrice * $componentQty,
                        ];
                        $isFirstComp = false;
                    }
                    continue;
                }

                // ── Regular item ──────────────────────────────────────────────
                $product     = Product::with('productUnits')->findOrFail($item['product_id']);
                $sellingUnit = $item['selling_unit'] ?? 'piece';
                $unitType    = $sellingUnit === 'carton' ? 'carton' : 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $expectedPrice = round((float) ($productUnit->selling_price ?? 0), 2);

                $unitPrice = round($item['unit_price'], 2);
                if (bccomp((string)$unitPrice, (string)$expectedPrice, 2) !== 0) {
                    $unitPrice = $expectedPrice;
                }

                $lineTotal    = $item['quantity'] * $unitPrice;
                $lineDisc     = $item['discount_amount'] ?? 0;
                $lineTax      = $item['tax_amount']      ?? 0;
                $lineSubtotal = $lineTotal - $lineDisc + $lineTax;

                $subtotal       += $lineTotal;
                $discountAmount += $lineDisc;
                $taxAmount      += $lineTax;

                $itemsData[] = [
                    'product_id'      => $product->id,
                    'product_set_id'  => null,
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $unitPrice,
                    'selling_unit'    => $sellingUnit,
                    'discount_amount' => $lineDisc,
                    'discount_type'   => $item['discount_type']  ?? null,
                    'discount_value'  => $item['discount_value'] ?? null,
                    'tax_amount'      => $lineTax,
                    'subtotal'        => $lineSubtotal,
                ];
            }

            $grandTotal    = $subtotal - $discountAmount + $taxAmount;
            $changeAmount  = $request->paid_amount - $grandTotal;

            // 2. Create Sale
            $sale = Sale::create([
                'customer_id'     => $request->customer_id,
                'discount_id'     => $request->discount_id,
                'tax_id'          => $request->tax_id,
                'user_id'         => auth()->id(),
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $request->paid_amount,
                'change_amount'   => max(0, $changeAmount),
                'payment_method'  => $request->payment_method,
                'status'          => 'completed',
                'notes'           => $request->notes,
            ]);

            // 3. Create Items & Deduct Stock
            foreach ($itemsData as $index => $itemData) {
                $sale->items()->create($itemData);

                $product     = Product::with('productUnits')->find($itemData['product_id']);
                $before      = $product->stock_quantity;
                $unitType    = $itemData['selling_unit'] ?? 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $packSize     = $productUnit ? $productUnit->uom : 1;
                $decrementQty = $unitType === 'carton' ? $itemData['quantity'] * $packSize : $itemData['quantity'];
                $product->decrement('stock_quantity', $decrementQty);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'sale',
                    'quantity'        => -$decrementQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $before - $decrementQty,
                    'reference'       => $sale->invoice_no,
                ]);
            }
        });

        $sale = Sale::where('user_id', auth()->id())->latest()->first();
        return redirect()->route('sales.show', $sale)->with('success', 'Sale completed!');
    }

    public function index(Request $request) {
        $sales = Sale::with(['customer', 'user'])
            ->when($request->search, fn($q) => $q->where('invoice_no', 'like', "%{$request->search}%"))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale) {
        $sale->load(['items.product', 'items.productSet.items.product', 'customer', 'user', 'discount', 'tax']);
        return view('sales.show', compact('sale'));
    }

    /** Print invoice view */
    public function invoice(Sale $sale) {
        $sale->load(['items.product.unit', 'items.productSet.items.product', 'customer', 'user']);
        return view('sales.invoice', compact('sale'));
    }

    /** Print new invoice view */
    public function invoiceNew(Sale $sale) {
        $sale->load(['items.product.unit', 'items.product.brand', 'items.productSet.items', 'customer', 'user']);
        return view('sales.invoice_new', compact('sale'));
    }

    /** Edit POS screen — pre-populated with existing sale */
    public function edit(Sale $sale) {
        abort_unless($sale->status === 'completed', 403, 'Only completed sales can be edited.');

        $customers   = \App\Models\Customer::where('is_active', true)->orderBy('name')->get();
        $discounts   = \App\Models\Discount::where('is_active', true)->get();
        $taxes       = \App\Models\Tax::where('is_active', true)->get();
        $brands      = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $products    = Product::with(['brand', 'tax', 'productUnits' => fn($q) => $q->where('is_active', true)->orderBy('unit_type')])
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get();
        $productSets = ProductSet::with(['items.product.productUnits'])
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get();

        $sale->load(['items.product.productUnits', 'items.productSet.items', 'customer', 'discount', 'tax']);

        // Build initial cart state for the JS
        $initialCart = [];
        $seenSetIds  = [];

        foreach ($sale->items as $item) {
            if ($item->product_set_id) {
                if (in_array($item->product_set_id, $seenSetIds)) continue;
                $seenSetIds[] = $item->product_set_id;

                $pSet    = $item->productSet;
                if (!$pSet) continue;

                // Derive how many sets were sold
                $defItem = $pSet->items->firstWhere('product_id', $item->product_id);
                $setsQty = ($defItem && $defItem->quantity > 0)
                    ? max(1, (int) round($item->quantity / $defItem->quantity)) : 1;

                $firstComp = $sale->items->where('product_set_id', $item->product_set_id)->first();

                $initialCart[] = [
                    'isSet'        => true,
                    'setId'        => $pSet->id,
                    'name'         => $pSet->name,
                    'price'        => (float) $pSet->selling_price,
                    'qty'          => $setsQty,
                    'discountType' => $firstComp->discount_type ?? 'pct',
                    'discountVal'  => (float) ($firstComp->discount_value ?? 0),
                    'setItems'     => $pSet->items->map(fn($si) => [
                        'product_id' => $si->product_id,
                        'unit_type'  => $si->unit_type,
                        'quantity'   => $si->quantity,
                        'pack_size'  => optional($si->product->productUnits->firstWhere('unit_type', $si->unit_type))->uom ?? 1,
                        'stock'      => (int) ($si->product->stock_quantity ?? 0) + ($si->quantity * $setsQty),
                    ])->values()->toArray(),
                    'maxQty'       => $setsQty + 999,
                    'taxRate'      => 0,
                ];
            } else {
                $product  = $item->product;
                $unitType = $item->selling_unit ?? 'piece';
                $pu       = $product?->productUnits->firstWhere('unit_type', $unitType)
                         ?? $product?->productUnits->firstWhere('unit_type', 'piece');

                $initialCart[] = [
                    'isSet'        => false,
                    'productId'    => $item->product_id,
                    'name'         => $product ? trim(($product->brand_name ?? '') . ' ' . $product->name) : 'Deleted Product',
                    'unitType'     => $unitType,
                    'price'        => (float) $item->unit_price,
                    'qty'          => (int) $item->quantity,
                    'discountType' => $item->discount_type ?? 'pct',
                    'discountVal'  => (float) ($item->discount_value ?? 0),
                    'taxRate'      => (float) ($product?->tax->rate ?? 0),
                    'maxQty'       => (int) ($product?->stock_quantity ?? 0) + (int) $item->quantity,
                ];
            }
        }

        $editSale = $sale;

        return view('sales.create', compact(
            'customers', 'discounts', 'taxes', 'brands', 'products', 'productSets', 'editSale', 'initialCart'
        ));
    }

    /** Update an existing sale */
    public function update(Request $request, Sale $sale) {
        abort_unless($sale->status === 'completed', 403, 'Only completed sales can be edited.');

        $request->validate([
            'customer_id'      => 'nullable|exists:customers,id',
            'discount_id'      => 'nullable|exists:discounts,id',
            'payment_method'   => 'required|in:cash,card,mobile,credit',
            'paid_amount'      => 'required|numeric|min:0',
            'items'            => 'required|array|min:1',
            'items.*.product_id'     => 'nullable|exists:products,id',
            'items.*.product_set_id' => 'nullable|exists:product_sets,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.selling_unit'   => 'nullable|in:piece,carton',
            'items.*.discount_type'  => 'nullable|string|in:pct,amt',
            'items.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $sale) {
            // 1. Restore stock from old items
            foreach ($sale->items as $oldItem) {
                $product     = Product::with('productUnits')->find($oldItem->product_id);
                if (!$product) continue;
                $unitType    = $oldItem->selling_unit ?? 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $packSize    = $productUnit ? $productUnit->uom : 1;
                $restoreQty  = $unitType === 'carton' ? $oldItem->quantity * $packSize : $oldItem->quantity;

                $before = $product->stock_quantity;
                $product->increment('stock_quantity', $restoreQty);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'return',
                    'quantity'        => $restoreQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $before + $restoreQty,
                    'reference'       => $sale->invoice_no . '-EDIT',
                ]);
            }

            // 2. Delete old items
            $sale->items()->delete();

            // 3. Re-process new items (same logic as store)
            $subtotal = $discountAmount = $taxAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                if (!empty($item['product_set_id'])) {
                    $set      = ProductSet::with('items.product.productUnits')->findOrFail($item['product_set_id']);
                    $setsQty  = (int) $item['quantity'];
                    $setPrice = round((float) $item['unit_price'], 2);
                    $lineTotal = $setPrice * $setsQty;
                    $lineDisc  = $item['discount_amount'] ?? 0;
                    $lineTax   = $item['tax_amount'] ?? 0;
                    $subtotal       += $lineTotal;
                    $discountAmount += $lineDisc;
                    $taxAmount      += $lineTax;
                    $setDiscType  = $item['discount_type']  ?? null;
                    $setDiscValue = $item['discount_value'] ?? null;
                    $isFirstComp  = true;
                    foreach ($set->items as $setItem) {
                        $componentQty = $setItem->quantity * $setsQty;
                        $productUnit  = $setItem->product->productUnits->firstWhere('unit_type', $setItem->unit_type)
                                     ?? $setItem->product->productUnits->firstWhere('unit_type', 'piece');
                        $unitPrice    = round((float) ($productUnit->selling_price ?? 0), 2);
                        $itemsData[] = [
                            'product_id'      => $setItem->product_id,
                            'product_set_id'  => $set->id,
                            'quantity'        => $componentQty,
                            'unit_price'      => $unitPrice,
                            'selling_unit'    => $setItem->unit_type,
                            'discount_amount' => $isFirstComp ? $lineDisc : 0,
                            'discount_type'   => $isFirstComp ? $setDiscType  : null,
                            'discount_value'  => $isFirstComp ? $setDiscValue : null,
                            'tax_amount'      => 0,
                            'subtotal'        => $unitPrice * $componentQty,
                        ];
                        $isFirstComp = false;
                    }
                    continue;
                }

                $product     = Product::with('productUnits')->findOrFail($item['product_id']);
                $sellingUnit = $item['selling_unit'] ?? 'piece';
                $unitType    = $sellingUnit === 'carton' ? 'carton' : 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $unitPrice    = round((float) ($productUnit->selling_price ?? $item['unit_price']), 2);
                $lineTotal    = $item['quantity'] * $unitPrice;
                $lineDisc     = $item['discount_amount'] ?? 0;
                $lineTax      = $item['tax_amount'] ?? 0;
                $subtotal       += $lineTotal;
                $discountAmount += $lineDisc;
                $taxAmount      += $lineTax;
                $itemsData[] = [
                    'product_id'      => $product->id,
                    'product_set_id'  => null,
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $unitPrice,
                    'selling_unit'    => $sellingUnit,
                    'discount_amount' => $lineDisc,
                    'discount_type'   => $item['discount_type']  ?? null,
                    'discount_value'  => $item['discount_value'] ?? null,
                    'tax_amount'      => $lineTax,
                    'subtotal'        => $lineTotal - $lineDisc + $lineTax,
                ];
            }

            $grandTotal   = $subtotal - $discountAmount + $taxAmount;
            $changeAmount = $request->paid_amount - $grandTotal;

            // 4. Update sale record
            $sale->update([
                'customer_id'     => $request->customer_id,
                'discount_id'     => $request->discount_id,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $request->paid_amount,
                'change_amount'   => max(0, $changeAmount),
                'payment_method'  => $request->payment_method,
                'notes'           => $request->notes,
            ]);

            // 5. Create new items & deduct stock
            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);
                $product     = Product::with('productUnits')->find($itemData['product_id']);
                if (!$product) continue;
                $unitType    = $itemData['selling_unit'] ?? 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $packSize    = $productUnit ? $productUnit->uom : 1;
                $deductQty   = $unitType === 'carton' ? $itemData['quantity'] * $packSize : $itemData['quantity'];
                $before      = $product->stock_quantity;
                $product->decrement('stock_quantity', $deductQty);
                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'sale',
                    'quantity'        => -$deductQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $before - $deductQty,
                    'reference'       => $sale->invoice_no . '-EDITED',
                ]);
            }
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated successfully.');
    }

    /** Cancel a sale and restore stock */
    public function cancel(Sale $sale) {
        abort_unless(auth()->user()->can('cancel_sale'), 403, 'You do not have permission to cancel sales.');

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Only completed sales can be cancelled.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $product     = $item->product->load('productUnits');
                $unitType    = $item->selling_unit ?? 'piece';
                $productUnit = $product->productUnits->firstWhere('unit_type', $unitType)
                            ?? $product->productUnits->firstWhere('unit_type', 'piece');
                $packSize    = $productUnit ? $productUnit->uom : 1;
                $restoreQty  = $unitType === 'carton' ? $item->quantity * $packSize : $item->quantity;

                $before = $product->stock_quantity;
                $product->increment('stock_quantity', $restoreQty);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'return',
                    'quantity'        => $restoreQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $before + $restoreQty,
                    'reference'       => $sale->invoice_no . '-CANCELLED',
                ]);
            }
            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.index')->with('success', 'Sale cancelled and stock restored.');
    }
}