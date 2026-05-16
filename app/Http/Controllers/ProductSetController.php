<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSet;
use App\Models\ProductSetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductSetController extends Controller
{
    public function index(Request $request)
    {
        $sets = ProductSet::withCount('items')
            ->with(['items.product'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null, fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(10);

        return view('product-sets.index', compact('sets'));
    }

    public function create()
    {
        $products = Product::with('productUnits')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('product-sets.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'code'          => 'required|string|max:60|unique:product_sets,code',
            'selling_price' => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_type'  => 'required|in:piece,carton',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $imagePath = $request->file('image')
                ? $request->file('image')->store('product-sets', 'public')
                : null;

            $set = ProductSet::create([
                'name'          => $request->name,
                'code'          => strtoupper($request->code),
                'description'   => $request->description,
                'image'         => $imagePath,
                'selling_price' => $request->selling_price,
                'is_active'     => $request->boolean('is_active', true),
                'created_by'    => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $set->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_type'  => $item['unit_type'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('product-sets.index')->with('success', 'Product set created.');
    }

    public function show(ProductSet $productSet)
    {
        $productSet->load(['items.product.productUnits', 'creator']);
        return view('product-sets.show', compact('productSet'));
    }

    public function edit(ProductSet $productSet)
    {
        $productSet->load('items.product');
        $products = Product::with('productUnits')->where('is_active', true)->orderBy('name')->get();
        return view('product-sets.edit', compact('productSet', 'products'));
    }

    public function update(Request $request, ProductSet $productSet)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'code'          => 'required|string|max:60|unique:product_sets,code,' . $productSet->id,
            'selling_price' => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_type'  => 'required|in:piece,carton',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $productSet) {
            $imagePath = $productSet->image;
            if ($request->file('image')) {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = $request->file('image')->store('product-sets', 'public');
            }

            $productSet->update([
                'name'          => $request->name,
                'code'          => strtoupper($request->code),
                'description'   => $request->description,
                'image'         => $imagePath,
                'selling_price' => $request->selling_price,
                'is_active'     => $request->boolean('is_active', true),
            ]);

            $productSet->items()->delete();
            foreach ($request->items as $item) {
                $productSet->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_type'  => $item['unit_type'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('product-sets.index')->with('success', 'Product set updated.');
    }

    public function destroy(ProductSet $productSet)
    {
        if ($productSet->image) {
            Storage::disk('public')->delete($productSet->image);
        }
        $productSet->delete();
        return back()->with('success', 'Product set deleted.');
    }

    public function toggle(ProductSet $productSet)
    {
        $productSet->update(['is_active' => !$productSet->is_active]);
        return back()->with('success', 'Product set ' . ($productSet->is_active ? 'enabled' : 'disabled') . '.');
    }
}
