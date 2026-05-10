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
            ->paginate(20)
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
        $products  = Product::with(['unit'])->where('is_active', true)->orderBy('name')->get();

        $productsJson = $products->map(function ($p) {
            return [
                'id'    => $p->id,
                'name'  => $p->name,
                'code'  => $p->code,
                'price' => (float) $p->buying_price,
                'unit'  => optional($p->unit)->abbreviation ?? '',
            ];
        })->values()->all();

        return view('purchases.create', compact('suppliers', 'products', 'productsJson'));
    }

    // ── Store ─────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'purchase_date'      => 'required|date',
            'paid_amount'        => 'required|numeric|min:0',
            'status'             => 'required|in:received,pending,cancelled',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $grandTotal = 0;
            $itemsData  = [];

            foreach ($request->items as $item) {
                $subtotal    = $item['quantity'] * $item['unit_cost'];
                $grandTotal += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'subtotal'   => $subtotal,
                ];
            }

            $paidAmount = min((float) $request->paid_amount, $grandTotal);

            $purchase = Purchase::create([
                'supplier_id'     => $request->supplier_id,
                'user_id'         => auth()->id(),
                'purchase_date'   => $request->purchase_date,
                'subtotal'        => $grandTotal, // no line-level tax/discount
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paidAmount,
                'status'          => $request->status,
                'notes'           => $request->notes,
            ]);

            foreach ($itemsData as $itemData) {
                $purchase->items()->create($itemData);

                if ($request->status === 'received') {
                    $product = Product::find($itemData['product_id']);
                    $before  = $product->stock_quantity;
                    $product->increment('stock_quantity', $itemData['quantity']);
                    $product->update(['buying_price' => $itemData['unit_cost']]);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'purchase',
                        'quantity'        => $itemData['quantity'],
                        'before_quantity' => $before,
                        'after_quantity'  => $before + $itemData['quantity'],
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
        $products  = Product::with(['unit'])->where('is_active', true)->orderBy('name')->get();
        $purchase->load(['items.product']);

        $productsJson = $products->map(function ($p) {
            return [
                'id'    => $p->id,
                'name'  => $p->name,
                'code'  => $p->code,
                'price' => (float) $p->buying_price,
                'unit'  => optional($p->unit)->abbreviation ?? '',
            ];
        })->values()->all();

        $existingJson = $purchase->items->map(function ($i) {
            return [
                'product_id' => $i->product_id,
                'quantity'   => $i->quantity,
                'unit_cost'  => (float) $i->unit_cost,
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
            'supplier_id'        => 'required|exists:suppliers,id',
            'purchase_date'      => 'required|date',
            'paid_amount'        => 'required|numeric|min:0',
            'status'             => 'required|in:received,pending,cancelled',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $wasReceived = $purchase->status === 'received';
            $grandTotal  = 0;
            $itemsData   = [];

            foreach ($request->items as $item) {
                $subtotal    = $item['quantity'] * $item['unit_cost'];
                $grandTotal += $subtotal;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'subtotal'   => $subtotal,
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
                $purchase->items()->create($itemData);

                if (!$wasReceived && $request->status === 'received') {
                    $product = Product::find($itemData['product_id']);
                    $before  = $product->stock_quantity;
                    $product->increment('stock_quantity', $itemData['quantity']);
                    $product->update(['buying_price' => $itemData['unit_cost']]);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'purchase',
                        'quantity'        => $itemData['quantity'],
                        'before_quantity' => $before,
                        'after_quantity'  => $before + $itemData['quantity'],
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
                    $product = $item->product;
                    $before  = $product->stock_quantity;
                    $product->decrement('stock_quantity', $item->quantity);

                    StockMovement::create([
                        'product_id'      => $product->id,
                        'user_id'         => auth()->id(),
                        'type'            => 'return',
                        'quantity'        => -$item->quantity,
                        'before_quantity' => $before,
                        'after_quantity'  => $before - $item->quantity,
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
                $product = $item->product;
                $before  = $product->stock_quantity;
                $product->increment('stock_quantity', $item->quantity);
                $product->update(['buying_price' => $item->unit_cost]);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'purchase',
                    'quantity'        => $item->quantity,
                    'before_quantity' => $before,
                    'after_quantity'  => $before + $item->quantity,
                    'reference'       => $purchase->reference_no,
                ]);
            }
            $purchase->update(['status' => 'received']);
        });

        return redirect()->route('purchases.show', $purchase)
                         ->with('success', 'Purchase received and stock updated.');
    }
}