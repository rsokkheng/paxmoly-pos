<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/TaxController.php
// ─────────────────────────────────────────────────────────────────



// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/UnitController.php
// ─────────────────────────────────────────────────────────────────
class UnitController extends Controller
{
    public function index() {
        $units = \App\Models\Unit::withCount('products')->paginate(15);
        return view('units.index', compact('units'));
    }
    public function create() { return view('units.create'); }
    public function store(Request $request) {
        $request->validate([
            'name'       => 'required|string|max:100|unique:units',
            'short_name' => 'required|string|max:20',
        ]);
        \App\Models\Unit::create($request->only('name', 'short_name'));
        return redirect()->route('units.index')->with('success', 'Unit created.');
    }
    public function edit(\App\Models\Unit $unit) { return view('units.edit', compact('unit')); }
    public function update(Request $request, \App\Models\Unit $unit) {
        $request->validate([
            'name'       => 'required|string|max:100|unique:units,name,' . $unit->id,
            'short_name' => 'required|string|max:20',
        ]);
        $unit->update($request->only('name', 'short_name'));
        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }
    public function destroy(\App\Models\Unit $unit) {
        if ($unit->products()->count()) {
            return back()->with('error', 'Unit is used by products.');
        }
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}