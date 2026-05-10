<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])
            ->when($request->input('product_id'), fn($q, $id) => $q->where('product_id', $id))
            ->when($request->input('type'),       fn($q, $t)  => $q->where('type', $t))
            ->when($request->input('date_from'),  fn($q, $d)  => $q->whereDate('created_at', '>=', $d))
            ->when($request->input('date_to'),    fn($q, $d)  => $q->whereDate('created_at', '<=', $d));

        $movements = $query->latest()->paginate(30)->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'code']);

        $summary = StockMovement::selectRaw("
            SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as total_out,
            COUNT(*) as total_movements
        ")->first();

        return view('inventory.movements', compact('movements', 'products', 'summary'));
    }

    public function adjustCreate()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'stock_quantity']);
        return view('inventory.adjust', compact('products'));
    }

    public function adjustStore(Request $request)
    {
        abort_unless(auth()->user()->can('adjust_stock'), 403, 'You do not have permission to adjust stock.');

        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'adjustment_type' => 'required|in:add,remove',
            'quantity'        => 'required|integer|min:1',
            'reason'          => 'required|string|max:100',
            'notes'           => 'nullable|string|max:500',
        ]);

        $userId = (int) auth()->id();

        DB::transaction(function () use ($request, $userId) {
            /** @var Product $product */
            $product  = Product::lockForUpdate()->findOrFail($request->input('product_id'));
            $qty      = (int) $request->input('quantity');
            $isAdd    = $request->input('adjustment_type') === 'add';
            $delta    = $isAdd ? $qty : -$qty;

            if (!$isAdd && $product->stock_quantity < $qty) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => "Cannot remove {$qty} units — only {$product->stock_quantity} in stock.",
                ]);
            }

            $before = $product->stock_quantity;
            $product->increment('stock_quantity', $delta);

            StockMovement::create([
                'product_id'      => $product->getKey(),
                'user_id'         => $userId,
                'type'            => 'adjustment',
                'quantity'        => $delta,
                'before_quantity' => $before,
                'after_quantity'  => $before + $delta,
                'reference'       => 'ADJ-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'notes'           => '[' . $request->input('reason') . '] ' . $request->input('notes'),
            ]);
        });

        return redirect()->route('inventory.movements')
                         ->with('success', 'Stock adjustment saved successfully.');
    }
}
