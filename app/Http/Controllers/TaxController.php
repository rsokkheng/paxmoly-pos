<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/TaxController.php
// ─────────────────────────────────────────────────────────────────
class TaxController extends Controller
{
    public function index() {
        $taxes = \App\Models\Tax::latest()->paginate(15);
        return view('taxes.index', compact('taxes'));
    }
    public function create() { return view('taxes.create'); }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100|unique:taxes',
            'rate' => 'required|numeric|min:0|max:100',
        ]);
        \App\Models\Tax::create($request->only('name', 'rate', 'is_active'));
        return redirect()->route('taxes.index')->with('success', 'Tax created.');
    }
    public function edit(\App\Models\Tax $tax) { return view('taxes.edit', compact('tax')); }
    public function update(Request $request, \App\Models\Tax $tax) {
        $request->validate([
            'name' => 'required|string|max:100|unique:taxes,name,' . $tax->id,
            'rate' => 'required|numeric|min:0|max:100',
        ]);
        $tax->update($request->only('name', 'rate', 'is_active'));
        return redirect()->route('taxes.index')->with('success', 'Tax updated.');
    }
    public function destroy(\App\Models\Tax $tax) {
        $tax->delete();
        return redirect()->route('taxes.index')->with('success', 'Tax deleted.');
    }
}