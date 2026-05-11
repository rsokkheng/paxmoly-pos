<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/ProductController.php
// ─────────────────────────────────────────────────────────────────
class ProductController extends Controller
{
    public function index(Request $request) {
        $products = \App\Models\Product::with(['category', 'unit', 'brand'])
        ->when($request->search, function ($q, $search) {
            // Wrap in a closure so orWhere doesn't bleed into outer query
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('brand', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        })
        ->when($request->category_id, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        })
        ->when($request->low_stock, function ($q) {
            $q->whereColumn('stock_quantity', '<=', 'alert_quantity');
        })
        ->latest()
        ->paginate(20)
        ->withQueryString(); // preserve filters on paginated links
 
    $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
 
    return view('products.index', compact('products', 'categories'));
    }

    public function create() {
        $categories = \App\Models\Category::where('is_active', true)->get();
        $brands      = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $units       = \App\Models\Unit::all();
        $taxes       = \App\Models\Tax::where('is_active', true)->get();
        return view('products.create', compact('categories', 'brands', 'units', 'taxes'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'category_id'              => 'required|exists:categories,id',
            'brand_id'                 => 'nullable|exists:brands,id',
            'unit_id'                  => 'required|exists:units,id',
            'tax_id'                   => 'nullable|exists:taxes,id',
            'name'                     => 'required|string|max:255',
            'brand_name'               => 'nullable|string|max:255',
            'code'                     => 'required|string|max:100|unique:products',
            'barcode'                  => 'nullable|string|max:100',
            'description'              => 'nullable|string',
            'image'                    => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'buying_price'             => 'required|numeric|min:0',
            'selling_price'            => 'required|numeric|min:0',
            'stock_quantity'           => 'required|integer|min:0',
            'alert_quantity'           => 'required|integer|min:0',
            'is_active'                => 'boolean',
            'packing'                  => 'nullable|string|max:255',
        ]);

        $packSize = 1;
        if (!empty($data['packing']) && preg_match('/(\d+)/', $data['packing'], $matches)) {
            $packSize = (int) $matches[1];
        }
        if (!empty($data['brand_id'])) {
            $data['brand_name'] = \App\Models\Brand::find($data['brand_id'])->name;
        } else {
            $data['brand_name'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        \App\Models\Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(\App\Models\Product $product) {
        $product->load(['category', 'unit', 'tax', 'brand']);
        $stockMovements = $product->stockMovements()->latest()->limit(20)->get();
        return view('products.show', compact('product', 'stockMovements'));
    }

    public function edit(\App\Models\Product $product) {
        $categories = \App\Models\Category::where('is_active', true)->get();
        $brands      = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $units       = \App\Models\Unit::all();
        $taxes       = \App\Models\Tax::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories', 'brands', 'units', 'taxes'));
    }

    public function update(Request $request, \App\Models\Product $product) {
        $data = $request->validate([
            'category_id'              => 'required|exists:categories,id',
            'brand_id'                 => 'nullable|exists:brands,id',
            'unit_id'                  => 'required|exists:units,id',
            'tax_id'                   => 'nullable|exists:taxes,id',
            'name'                     => 'required|string|max:255',
            'brand_name'               => 'nullable|string|max:255',
            'code'                     => 'required|string|max:100|unique:products,code,' . $product->id,
            'barcode'                  => 'nullable|string|max:100',
            'description'              => 'nullable|string',
            'image'                    => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'buying_price'             => 'required|numeric|min:0',
            'selling_price'            => 'required|numeric|min:0',
            'stock_quantity'           => 'required|integer|min:0',
            'alert_quantity'           => 'required|integer|min:0',
            'is_active'                => 'boolean',
            'packing'                  => 'nullable|string|max:255',
        ]);

        $packSize = 1;
        if (!empty($data['packing']) && preg_match('/(\d+)/', $data['packing'], $matches)) {
            $packSize = (int) $matches[1];
        }
        if (!empty($data['brand_id'])) {
            $data['brand_name'] = \App\Models\Brand::find($data['brand_id'])->name;
        } else {
            $data['brand_name'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(\App\Models\Product $product) {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    // AJAX: search product by code/name for POS
    public function search(Request $request) {
        $q = $request->q;
        $products = \App\Models\Product::with(['unit', 'tax', 'brand'])
            ->where('is_active', true)
            ->where(fn($query) => $query
                ->where('name', 'like', "%$q%")
                ->orWhere('brand_name', 'like', "%$q%")
                ->orWhere('code', 'like', "%$q%")
                ->orWhere('barcode', 'like', "%$q%")
                ->orWhereHas('brand', fn($q2) => $q2->where('name', 'like', "%$q%")))
            ->limit(10)
            ->get();
        return response()->json($products);
    }
}
