<?php 
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /** POS Screen */
    public function create() {
        $customers  = \App\Models\Customer::where('is_active', true)->orderBy('name')->get();
        $discounts  = \App\Models\Discount::where('is_active', true)->get();
        $taxes      = \App\Models\Tax::where('is_active', true)->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $products   = Product::with(['category', 'tax'])
                        ->where('is_active', true)
                        ->where('stock_quantity', '>', 0)
                        ->orderBy('name')
                        ->get();
        return view('sales.create', compact('customers', 'discounts', 'taxes', 'categories', 'products'));
    }

    /** Save the sale */
    public function store(Request $request) {
        $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'discount_id'    => 'nullable|exists:discounts,id',
            'tax_id'         => 'nullable|exists:taxes,id',
            'payment_method' => 'required|in:cash,card,mobile,credit',
            'paid_amount'    => 'required|numeric|min:0',
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Calculate totals
            $subtotal        = 0;
            $discountAmount  = 0;
            $taxAmount       = 0;

            $itemsData = [];
            foreach ($request->items as $item) {
                $product    = Product::findOrFail($item['product_id']);
                $lineTotal  = $item['quantity'] * $item['unit_price'];
                $lineDisc   = $item['discount_amount'] ?? 0;
                $lineTax    = $item['tax_amount']      ?? 0;
                $lineSubtotal = $lineTotal - $lineDisc + $lineTax;

                $subtotal       += $lineTotal;
                $discountAmount += $lineDisc;
                $taxAmount      += $lineTax;

                $itemsData[] = [
                    'product_id'      => $product->id,
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_amount' => $lineDisc,
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
            foreach ($itemsData as $itemData) {
                $sale->items()->create($itemData);

                $product = Product::find($itemData['product_id']);
                $before  = $product->stock_quantity;
                $product->decrement('stock_quantity', $itemData['quantity']);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'sale',
                    'quantity'        => -$itemData['quantity'],
                    'before_quantity' => $before,
                    'after_quantity'  => $before - $itemData['quantity'],
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
            ->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale) {
        $sale->load(['items.product', 'customer', 'user', 'discount', 'tax']);
        return view('sales.show', compact('sale'));
    }

    /** Print invoice view */
    public function invoice(Sale $sale) {
        $sale->load(['items.product.unit', 'customer', 'user']);
        return view('sales.invoice', compact('sale'));
    }

    /** Cancel a sale and restore stock */
    public function cancel(Sale $sale) {
        abort_unless(auth()->user()->can('cancel_sale'), 403, 'You do not have permission to cancel sales.');

        if ($sale->status !== 'completed') {
            return back()->with('error', 'Only completed sales can be cancelled.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                $before  = $product->stock_quantity;
                $product->increment('stock_quantity', $item->quantity);

                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'return',
                    'quantity'        => $item->quantity,
                    'before_quantity' => $before,
                    'after_quantity'  => $before + $item->quantity,
                    'reference'       => $sale->invoice_no . '-CANCELLED',
                ]);
            }
            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.index')->with('success', 'Sale cancelled and stock restored.');
    }
}