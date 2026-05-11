<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'unit', 'brand', 'productUnits'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('brand_name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhereHas('brand', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock_quantity', '<=', 'alert_quantity'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = \App\Models\Category::where('is_active', true)->get();
        $brands     = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $units      = \App\Models\Unit::all();
        $taxes      = \App\Models\Tax::where('is_active', true)->get();
        return view('products.create', compact('categories', 'brands', 'units', 'taxes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'brand_id'           => 'nullable|exists:brands,id',
            'unit_id'            => 'required|exists:units,id',
            'tax_id'             => 'nullable|exists:taxes,id',
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:100|unique:products',
            'barcode'            => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'image'              => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'stock_quantity'     => 'required|integer|min:0',
            'alert_quantity'     => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            // PCS unit
            'pcs_buying_price'   => 'required|numeric|min:0',
            'pcs_selling_price'  => 'required|numeric|min:0',
            'pcs_barcode'        => 'nullable|string|max:100',
            // CASE unit
            'has_case'           => 'nullable',
            'case_label'         => 'nullable|string|max:20',
            'case_uom'           => 'nullable|integer|min:2',
            'case_buying_price'  => 'nullable|numeric|min:0',
            'case_selling_price' => 'nullable|numeric|min:0',
            'case_barcode'       => 'nullable|string|max:100',
        ]);

        $hasCase = (bool) $request->has_case;
        $caseUom = $hasCase ? max(2, (int) $request->case_uom) : 1;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $brandName = null;
        if ($request->brand_id) {
            $brandName = \App\Models\Brand::find($request->brand_id)->name ?? null;
        }

        DB::transaction(function () use ($request, $hasCase, $caseUom, $imagePath, $brandName) {
            $product = Product::create([
                'category_id'        => $request->category_id,
                'brand_id'           => $request->brand_id ?: null,
                'unit_id'            => $request->unit_id,
                'tax_id'             => $request->tax_id ?: null,
                'name'               => $request->name,
                'brand_name'         => $brandName,
                'code'               => $request->code,
                'barcode'            => $request->barcode ?: null,
                'description'        => $request->description,
                'image'              => $imagePath,
                'stock_quantity'     => $request->stock_quantity,
                'alert_quantity'     => $request->alert_quantity,
                'is_active'          => $request->boolean('is_active', true),
                // sync backward-compat columns
                'buying_price'        => $request->pcs_buying_price,
                'selling_price'       => $request->pcs_selling_price,
                'buying_price_case'   => $hasCase ? ($request->case_buying_price  ?? 0) : 0,
                'selling_price_case'  => $hasCase ? ($request->case_selling_price ?? 0) : 0,
                'uom_pcs'             => 1,
                'uom_case'            => $caseUom,
            ]);

            $product->productUnits()->create([
                'unit_type'     => 'piece',
                'label'         => 'PCS',
                'uom'           => 1,
                'barcode'       => $request->pcs_barcode ?: null,
                'buying_price'  => $request->pcs_buying_price,
                'selling_price' => $request->pcs_selling_price,
                'is_active'     => true,
            ]);

            if ($hasCase && $caseUom >= 2) {
                $product->productUnits()->create([
                    'unit_type'     => 'carton',
                    'label'         => $request->case_label ?: 'CASE',
                    'uom'           => $caseUom,
                    'barcode'       => $request->case_barcode ?: null,
                    'buying_price'  => $request->case_buying_price  ?? 0,
                    'selling_price' => $request->case_selling_price ?? 0,
                    'is_active'     => true,
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit', 'tax', 'brand', 'productUnits']);
        $stockMovements = $product->stockMovements()->latest()->limit(20)->get();
        return view('products.show', compact('product', 'stockMovements'));
    }

    public function edit(Product $product)
    {
        $product->load('productUnits');
        $categories = \App\Models\Category::where('is_active', true)->get();
        $brands     = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        $units      = \App\Models\Unit::all();
        $taxes      = \App\Models\Tax::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories', 'brands', 'units', 'taxes'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'        => 'required|exists:categories,id',
            'brand_id'           => 'nullable|exists:brands,id',
            'unit_id'            => 'required|exists:units,id',
            'tax_id'             => 'nullable|exists:taxes,id',
            'name'               => 'required|string|max:255',
            'code'               => 'required|string|max:100|unique:products,code,' . $product->id,
            'barcode'            => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'image'              => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'stock_quantity'     => 'required|integer|min:0',
            'alert_quantity'     => 'required|integer|min:0',
            'is_active'          => 'nullable|boolean',
            'pcs_buying_price'   => 'required|numeric|min:0',
            'pcs_selling_price'  => 'required|numeric|min:0',
            'pcs_barcode'        => 'nullable|string|max:100',
            'has_case'           => 'nullable',
            'case_label'         => 'nullable|string|max:20',
            'case_uom'           => 'nullable|integer|min:2',
            'case_buying_price'  => 'nullable|numeric|min:0',
            'case_selling_price' => 'nullable|numeric|min:0',
            'case_barcode'       => 'nullable|string|max:100',
        ]);

        $hasCase = (bool) $request->has_case;
        $caseUom = $hasCase ? max(2, (int) $request->case_uom) : 1;

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $brandName = null;
        if ($request->brand_id) {
            $brandName = \App\Models\Brand::find($request->brand_id)->name ?? null;
        }

        DB::transaction(function () use ($request, $product, $hasCase, $caseUom, $imagePath, $brandName) {
            $product->update([
                'category_id'        => $request->category_id,
                'brand_id'           => $request->brand_id ?: null,
                'unit_id'            => $request->unit_id,
                'tax_id'             => $request->tax_id ?: null,
                'name'               => $request->name,
                'brand_name'         => $brandName,
                'code'               => $request->code,
                'barcode'            => $request->barcode ?: null,
                'description'        => $request->description,
                'image'              => $imagePath,
                'stock_quantity'     => $request->stock_quantity,
                'alert_quantity'     => $request->alert_quantity,
                'is_active'          => $request->boolean('is_active', true),
                'buying_price'       => $request->pcs_buying_price,
                'selling_price'      => $request->pcs_selling_price,
                'buying_price_case'  => $hasCase ? ($request->case_buying_price  ?? 0) : 0,
                'selling_price_case' => $hasCase ? ($request->case_selling_price ?? 0) : 0,
                'uom_pcs'            => 1,
                'uom_case'           => $caseUom,
            ]);

            $product->productUnits()->updateOrCreate(
                ['unit_type' => 'piece'],
                [
                    'label'         => 'PCS',
                    'uom'           => 1,
                    'barcode'       => $request->pcs_barcode ?: null,
                    'buying_price'  => $request->pcs_buying_price,
                    'selling_price' => $request->pcs_selling_price,
                    'is_active'     => true,
                ]
            );

            if ($hasCase && $caseUom >= 2) {
                $product->productUnits()->updateOrCreate(
                    ['unit_type' => 'carton'],
                    [
                        'label'         => $request->case_label ?: 'CASE',
                        'uom'           => $caseUom,
                        'barcode'       => $request->case_barcode ?: null,
                        'buying_price'  => $request->case_buying_price  ?? 0,
                        'selling_price' => $request->case_selling_price ?? 0,
                        'is_active'     => true,
                    ]
                );
            } else {
                $product->productUnits()->where('unit_type', 'carton')->delete();
            }
        });

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function search(Request $request)
    {
        $q        = $request->q;
        $products = Product::with(['unit', 'tax', 'brand', 'productUnits'])
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
