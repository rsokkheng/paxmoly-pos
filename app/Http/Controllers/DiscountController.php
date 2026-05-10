<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;

// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/DiscountController.php
// ─────────────────────────────────────────────────────────────────
class DiscountController extends Controller
{
    public function index() {
        $discounts = \App\Models\Discount::latest()->paginate(15);
        return view('discounts.index', compact('discounts'));
    }
    public function create() { return view('discounts.create'); }
    public function store(Request $request) {
        $request->validate([
            'code'       => 'required|string|max:50|unique:discounts,code',
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percentage,fixed',
            'value'      => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);
        \App\Models\Discount::create($request->all());
        return redirect()->route('discounts.index')->with('success', 'Discount created.');
    }
    public function edit(\App\Models\Discount $discount) {
        return view('discounts.edit', compact('discount'));
    }
    public function update(Request $request, \App\Models\Discount $discount) {
        $request->validate([
            'code'       => 'required|string|max:50|unique:discounts,code',
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percentage,fixed',
            'value'      => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);
        $discount->update($request->all());
        return redirect()->route('discounts.index')->with('success', 'Discount updated.');
    }
    public function destroy(\App\Models\Discount $discount) {
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', 'Discount deleted.');
    }
}
