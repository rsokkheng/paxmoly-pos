<?php 
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/SupplierController.php
// ─────────────────────────────────────────────────────────────────
class SupplierController extends Controller
{
    public function index() {
        $suppliers = Supplier::withCount('purchases')->latest()->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }
    public function create() { return view('suppliers.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'company'   => 'nullable|string|max:255',
            'email'     => 'nullable|email|unique:suppliers',
            'phone'     => 'required|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }
    public function show(Supplier $supplier) {
        $supplier->load(['purchases' => fn($q) => $q->latest()->limit(10)]);
        return view('suppliers.show', compact('supplier'));
    }
    public function edit(Supplier $supplier) {
        return view('suppliers.edit', compact('supplier'));
    }
    public function update(Request $request, Supplier $supplier) {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'company'   => 'nullable|string|max:255',
            'email'     => 'nullable|email|unique:suppliers,email,' . $supplier->id,
            'phone'     => 'required|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }
    public function destroy(Supplier $supplier) {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}