<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;

// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/CustomerController.php
// ─────────────────────────────────────────────────────────────────
class CustomerController extends Controller
{
    public function index() {
        $customers = \App\Models\Customer::withCount('sales')->latest()->paginate(15);
        return view('customers.index', compact('customers'));
    }
    public function create() { return view('customers.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|unique:customers',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        \App\Models\Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }
    public function show(\App\Models\Customer $customer) {
        $sales = $customer->sales()->latest()->paginate(10);
        return view('customers.show', compact('customer', 'sales'));
    }
    public function edit(\App\Models\Customer $customer) {
        return view('customers.edit', compact('customer'));
    }
    public function update(Request $request, \App\Models\Customer $customer) {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }
    public function destroy(\App\Models\Customer $customer) {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}