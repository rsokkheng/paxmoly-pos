<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')
            ->latest()
            ->paginate(15);

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        Brand::create($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $brand->update($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count()) {
            return back()->with('error', 'Cannot delete brand with assigned products.');
        }

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
