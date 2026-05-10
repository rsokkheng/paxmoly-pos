<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Unit, Tax, Category, Supplier, Customer, Product, Discount, User};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ─────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@pos.com',
            'password' => Hash::make('password'),
        ]);

        // ── Units ──────────────────────────────────────────────────────────
        $units = [
            ['name' => 'Piece',   'short_name' => 'Pcs'],
            ['name' => 'Kilogram','short_name' => 'Kg'],
            ['name' => 'Litre',   'short_name' => 'L'],
            ['name' => 'Box',     'short_name' => 'Box'],
            ['name' => 'Dozen',   'short_name' => 'Dz'],
        ];
        foreach ($units as $u) Unit::create($u);

        // ── Taxes ──────────────────────────────────────────────────────────
        $taxes = [
            ['name' => 'VAT 10%',    'rate' => 10.00, 'is_active' => true],
            ['name' => 'VAT 5%',     'rate' => 5.00,  'is_active' => true],
            ['name' => 'Tax Exempt', 'rate' => 0.00,  'is_active' => true],
        ];
        foreach ($taxes as $t) Tax::create($t);

        // ── Categories ────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Beverages',    'description' => 'Drinks and beverages'],
            ['name' => 'Food',         'description' => 'Food items'],
            ['name' => 'Electronics',  'description' => 'Electronic products'],
            ['name' => 'Clothing',     'description' => 'Clothes and apparel'],
            ['name' => 'Stationery',   'description' => 'Office and school supplies'],
        ];
        foreach ($categories as $c) Category::create(array_merge($c, ['is_active' => true]));

        // ── Suppliers ─────────────────────────────────────────────────────
        $suppliers = [
            ['name' => 'ABC Distributors', 'phone' => '012-345-678', 'email' => 'abc@supplier.com', 'city' => 'Phnom Penh'],
            ['name' => 'XYZ Wholesalers',  'phone' => '098-765-432', 'email' => 'xyz@supplier.com', 'city' => 'Siem Reap'],
        ];
        foreach ($suppliers as $s) Supplier::create(array_merge($s, ['is_active' => true]));

        // ── Customers ─────────────────────────────────────────────────────
        $customers = [
            ['name' => 'Walk-in Customer', 'phone' => null],
            ['name' => 'John Doe',  'phone' => '012-111-111', 'email' => 'john@email.com'],
            ['name' => 'Jane Smith','phone' => '012-222-222', 'email' => 'jane@email.com'],
        ];
        foreach ($customers as $c) Customer::create(array_merge($c, ['is_active' => true]));

        // ── Products ──────────────────────────────────────────────────────
        $products = [
            ['name' => 'Coca Cola 330ml', 'code' => 'BEV001', 'category_id' => 1, 'unit_id' => 1, 'buying_price' => 0.50, 'selling_price' => 1.00, 'stock_quantity' => 200, 'alert_quantity' => 20],
            ['name' => 'Water 500ml',     'code' => 'BEV002', 'category_id' => 1, 'unit_id' => 1, 'buying_price' => 0.20, 'selling_price' => 0.50, 'stock_quantity' => 500, 'alert_quantity' => 50],
            ['name' => 'Bread Loaf',      'code' => 'FOD001', 'category_id' => 2, 'unit_id' => 1, 'buying_price' => 1.00, 'selling_price' => 2.00, 'stock_quantity' => 50,  'alert_quantity' => 10],
            ['name' => 'USB Cable',       'code' => 'ELE001', 'category_id' => 3, 'unit_id' => 1, 'buying_price' => 2.00, 'selling_price' => 5.00, 'stock_quantity' => 30,  'alert_quantity' => 5],
            ['name' => 'A4 Paper (Ream)', 'code' => 'STA001', 'category_id' => 5, 'unit_id' => 1, 'buying_price' => 3.00, 'selling_price' => 6.00, 'stock_quantity' => 100, 'alert_quantity' => 10],
        ];
        foreach ($products as $p) Product::create(array_merge($p, ['is_active' => true]));

        // ── Discounts ─────────────────────────────────────────────────────
        Discount::create([
            'name'      => 'Member 10%',
            'type'      => 'percentage',
            'value'     => 10,
            'is_active' => true,
        ]);
        Discount::create([
            'name'      => '$5 Off',
            'type'      => 'fixed',
            'value'     => 5,
            'is_active' => true,
        ]);
    }
}
