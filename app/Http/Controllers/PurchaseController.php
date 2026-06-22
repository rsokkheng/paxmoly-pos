<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status,      fn($q, $s)  => $q->where('status', $s))
            ->when($request->date_from,   fn($q, $d)  => $q->whereDate('purchase_date', '>=', $d))
            ->when($request->date_to,     fn($q, $d)  => $q->whereDate('purchase_date', '<=', $d))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $totals = Purchase::when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->selectRaw('
                SUM(grand_total)                   as total,
                SUM(paid_amount)                   as paid,
                SUM(grand_total - paid_amount)     as due
            ')
            ->first();

        return view('purchases.index', compact('purchases', 'suppliers', 'totals'));
    }

    // ── Create ────────────────────────────────────────────────────
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::with(['unit', 'productUnits' => fn($q) => $q->where('is_active', true)])
                        ->where('is_active', true)->orderBy('name_en')->get();

        $productsJson = $products->map(function ($p) {
            $pcsUnit  = $p->productUnits->firstWhere('unit_type', 'piece');
            $caseUnit = $p->productUnits->firstWhere('unit_type', 'carton');
            return [
                'id'           => $p->id,
                'name'         => $p->name_en,
                'code'         => $p->code ?? $p->barcode ?? '',
                'barcode'      => $p->barcode ?? $p->code ?? '',
                'image'        => $p->image ? asset('storage/' . $p->image) : null,
                'is_active'    => (bool) $p->is_active,
                'buying_price' => (float) $p->buying_price,
                'unit'         => optional($p->unit)->short_name ?? '',
                'has_case'     => $caseUnit !== null,
                'case_uom'     => $caseUnit ? (int) $caseUnit->uom : 1,
                'case_label'   => $caseUnit ? $caseUnit->label : 'Case',
                'pcs_label'    => $pcsUnit  ? $pcsUnit->label  : 'PCS',
            ];
        })->values()->all();

        return view('purchases.create', compact('suppliers', 'products', 'productsJson'));
    }

    // ── Store ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'           => 'required|exists:suppliers,id',
            'purchase_date'         => 'required|date',
            'paid_amount'           => 'required|numeric|min:0',
            'status'                => 'required|in:received,pending,cancelled',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:1',
            'items.*.unit_cost'     => 'required|numeric|min:0',
            'items.*.selling_unit'  => 'nullable|in:piece,carton',
            'items.*.pack_size'     => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $grandTotal = 0;
            $itemsData  = [];

            foreach ($request->items as $item) {
                $qty         = (int) $item['quantity'];
                $unit        = $item['selling_unit'] ?? 'piece';
                $packSize    = (int) ($item['pack_size'] ?? 1);
                $subtotal    = $qty * $item['unit_cost'];
                $grandTotal += $subtotal;

                // Pieces added to stock: carton × pack_size, piece × 1
                $stockQty = $unit === 'carton' ? $qty * $packSize : $qty;

                $itemsData[] = [
                    'product_id'   => $item['product_id'],
                    'quantity'     => $qty,
                    'selling_unit' => $unit,
                    'pack_size'    => $packSize,
                    'unit_cost'    => $item['unit_cost'],
                    'subtotal'     => $subtotal,
                    '_stock_qty'   => $stockQty,
                    '_unit_cost_pcs' => $unit === 'carton' && $packSize > 1
                        ? round($item['unit_cost'] / $packSize, 4)
                        : $item['unit_cost'],
                ];
            }

            $paidAmount = min((float) $request->paid_amount, $grandTotal);

            $purchase = Purchase::create([
                'supplier_id'     => $request->supplier_id,
                'user_id'         => auth()->id(),
                'purchase_date'   => $request->purchase_date,
                'subtotal'        => $grandTotal,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paidAmount,
                'status'          => $request->status,
                'notes'           => $request->notes,
            ]);

            foreach ($itemsData as $itemData) {
                $stockQty      = $itemData['_stock_qty'];
                $unitCostPcs   = $itemData['_unit_cost_pcs'];
                unset($itemData['_stock_qty'], $itemData['_unit_cost_pcs']);

                $purchase->items()->create($itemData);

                if ($request->status === 'received') {
                    $product = Product::find($itemData['product_id']);
                    $before  = $product->stock_quantity;
                    $product->increment('stock_quantity', $stockQty);
                    $product->update(['buying_price' => $unitCostPcs]);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'purchase',
                        'quantity'        => $stockQty,
                        'before_quantity' => $before,
                        'after_quantity'  => $before + $stockQty,
                        'reference'       => $purchase->reference_no,
                    ]);
                }
            }
        });

        $purchase = Purchase::where('user_id', auth()->id())->latest()->first();
        return redirect()->route('purchases.show', $purchase)
                         ->with('success', 'Purchase order created successfully.');
    }

    // ── Show ──────────────────────────────────────────────────────
    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product.unit', 'supplier', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    // ── Edit ──────────────────────────────────────────────────────
    public function edit(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase)
                             ->with('error', 'Received purchases cannot be edited.');
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::with(['unit', 'productUnits' => fn($q) => $q->where('is_active', true)])
                        ->where('is_active', true)->orderBy('name_en')->get();
        $purchase->load(['items.product']);

        $productsJson = $products->map(function ($p) {
            $pcsUnit  = $p->productUnits->firstWhere('unit_type', 'piece');
            $caseUnit = $p->productUnits->firstWhere('unit_type', 'carton');
            return [
                'id'           => $p->id,
                'name'         => $p->name_en,
                'code'         => $p->code ?? $p->barcode ?? '',
                'barcode'      => $p->barcode ?? $p->code ?? '',
                'image'        => $p->image ? asset('storage/' . $p->image) : null,
                'is_active'    => (bool) $p->is_active,
                'buying_price' => (float) $p->buying_price,
                'unit'         => optional($p->unit)->short_name ?? '',
                'has_case'     => $caseUnit !== null,
                'case_uom'     => $caseUnit ? (int) $caseUnit->uom : 1,
                'case_label'   => $caseUnit ? $caseUnit->label : 'Case',
                'pcs_label'    => $pcsUnit  ? $pcsUnit->label  : 'PCS',
            ];
        })->values()->all();

        $existingJson = $purchase->items->map(function ($i) {
            return [
                'product_id'   => $i->product_id,
                'quantity'     => $i->quantity,
                'unit_cost'    => (float) $i->unit_cost,
                'selling_unit' => $i->selling_unit ?? 'piece',
                'pack_size'    => $i->pack_size    ?? 1,
            ];
        })->values()->all();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'productsJson', 'existingJson'));
    }

    // ── Update ────────────────────────────────────────────────────
    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->route('purchases.show', $purchase)
                             ->with('error', 'Received purchases cannot be edited.');
        }

        $request->validate([
            'supplier_id'           => 'required|exists:suppliers,id',
            'purchase_date'         => 'required|date',
            'paid_amount'           => 'required|numeric|min:0',
            'status'                => 'required|in:received,pending,cancelled',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:1',
            'items.*.unit_cost'     => 'required|numeric|min:0',
            'items.*.selling_unit'  => 'nullable|in:piece,carton',
            'items.*.pack_size'     => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $wasReceived = $purchase->status === 'received';
            $grandTotal  = 0;
            $itemsData   = [];

            foreach ($request->items as $item) {
                $qty      = (int) $item['quantity'];
                $unit     = $item['selling_unit'] ?? 'piece';
                $packSize = (int) ($item['pack_size'] ?? 1);
                $subtotal    = $qty * $item['unit_cost'];
                $grandTotal += $subtotal;
                $stockQty    = $unit === 'carton' ? $qty * $packSize : $qty;

                $itemsData[] = [
                    'product_id'     => $item['product_id'],
                    'quantity'       => $qty,
                    'selling_unit'   => $unit,
                    'pack_size'      => $packSize,
                    'unit_cost'      => $item['unit_cost'],
                    'subtotal'       => $subtotal,
                    '_stock_qty'     => $stockQty,
                    '_unit_cost_pcs' => $unit === 'carton' && $packSize > 1
                        ? round($item['unit_cost'] / $packSize, 4)
                        : $item['unit_cost'],
                ];
            }

            $paidAmount = min((float) $request->paid_amount, $grandTotal);

            $purchase->update([
                'supplier_id'     => $request->supplier_id,
                'purchase_date'   => $request->purchase_date,
                'subtotal'        => $grandTotal,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paidAmount,
                'status'          => $request->status,
                'notes'           => $request->notes,
            ]);

            $purchase->items()->delete();

            foreach ($itemsData as $itemData) {
                $stockQty    = $itemData['_stock_qty'];
                $unitCostPcs = $itemData['_unit_cost_pcs'];
                unset($itemData['_stock_qty'], $itemData['_unit_cost_pcs']);

                $purchase->items()->create($itemData);

                if (!$wasReceived && $request->status === 'received') {
                    $product = Product::find($itemData['product_id']);
                    $before  = $product->stock_quantity;
                    $product->increment('stock_quantity', $stockQty);
                    $product->update(['buying_price' => $unitCostPcs]);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'purchase',
                        'quantity'        => $stockQty,
                        'before_quantity' => $before,
                        'after_quantity'  => $before + $stockQty,
                        'reference'       => $purchase->reference_no,
                    ]);
                }
            }
        });

        return redirect()->route('purchases.show', $purchase)
                         ->with('success', 'Purchase order updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'received') {
            return back()->with('error', 'Received purchases cannot be deleted. Cancel it first.');
        }
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase order deleted.');
    }

    // ── Cancel ────────────────────────────────────────────────────
    public function cancel(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Already cancelled.');
        }

        $wasReceived = $purchase->status === 'received';

        DB::transaction(function () use ($purchase, $wasReceived) {
            if ($wasReceived) {
                foreach ($purchase->items as $item) {
                    $isCarton = $item->selling_unit === 'carton' && $item->pack_size > 1;
                    $stockQty = $isCarton ? $item->quantity * $item->pack_size : $item->quantity;

                    $product = $item->product;
                    $before  = $product->stock_quantity;
                    $product->decrement('stock_quantity', $stockQty);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'return',
                        'quantity'        => -$stockQty,
                        'before_quantity' => $before,
                        'after_quantity'  => $before - $stockQty,
                        'reference'       => $purchase->reference_no . '-CANCELLED',
                    ]);
                }
            }
            $purchase->update(['status' => 'cancelled']);
        });

        $msg = 'Purchase cancelled' . ($wasReceived ? ' and stock reversed.' : '.');
        return redirect()->route('purchases.show', $purchase)->with('success', $msg);
    }

    // ── Receive ───────────────────────────────────────────────────
    public function receive(Purchase $purchase)
    {
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Only pending purchases can be marked as received.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $isCarton  = $item->selling_unit === 'carton' && $item->pack_size > 1;
                $stockQty  = $isCarton ? $item->quantity * $item->pack_size : $item->quantity;
                $unitCostPcs = $isCarton
                    ? round($item->unit_cost / $item->pack_size, 4)
                    : $item->unit_cost;

                $product = $item->product;
                $before  = $product->stock_quantity;
                $product->increment('stock_quantity', $stockQty);
                $product->update(['buying_price' => $unitCostPcs]);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'purchase',
                    'quantity'        => $stockQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $before + $stockQty,
                    'reference'       => $purchase->reference_no,
                ]);
            }
            $purchase->update(['status' => 'received']);
        });

        return redirect()->route('purchases.show', $purchase)
                         ->with('success', 'Purchase received and stock updated.');
    }
}