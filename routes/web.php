<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CategoryController,
    TaxController,
    UnitController,
    SupplierController,
    CustomerController,
    ProductController,
    DiscountController,
    SaleController,
    PurchaseController,
    ReportController,
    InventoryController,
    UserController,
    ProfileController,
};

// ── Public ────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ── Language switch ───────────────────────────────────────────────
Route::get('/language/{locale}', function ($locale) {
    if (\in_array($locale, ['en', 'km'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('language.switch');

// ── Auth required ─────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard — all authenticated users
    Route::get('/dashboard', function () {
        $todaySales     = \App\Models\Sale::whereDate('created_at', today())->where('status','completed')->sum('grand_total');
        $totalOrders    = \App\Models\Sale::whereDate('created_at', today())->count();
        $lowStock       = \App\Models\Product::whereColumn('stock_quantity','<=','alert_quantity')->count();
        $totalCustomers = \App\Models\Customer::count();
        return view('dashboard', compact('todaySales','totalOrders','lowStock','totalCustomers'));
    })->name('dashboard');

    // ── Sales / POS — cashier+ ────────────────────────────────────
    Route::middleware('can:process_sale')->group(function () {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales',       [SaleController::class, 'store'])->name('sales.store');
    });
    Route::get('sales',                [SaleController::class, 'index'])  ->name('sales.index');
    Route::get('sales/{sale}',         [SaleController::class, 'show'])   ->name('sales.show');
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::patch('sales/{sale}/cancel',[SaleController::class, 'cancel']) ->name('sales.cancel')
         ->middleware('can:cancel_sale');

    // ── Customers — cashier+ ──────────────────────────────────────
    Route::resource('customers', CustomerController::class)
         ->middleware('can:manage_customers');

    // ── Reports ───────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->middleware('can:view_reports')->group(function () {
        Route::get('sales',        [ReportController::class, 'sales'])->name('sales');
        Route::get('sales-period', [ReportController::class, 'salesByPeriod'])->name('sales_period');
        Route::get('top-products', [ReportController::class, 'topProducts'])->name('top_products');
        Route::get('stock',        [ReportController::class, 'stock'])->name('stock');
        // Profit report — manager+
        Route::get('profit', [ReportController::class, 'profit'])
             ->name('profit')->middleware('can:view_profit_report');

        Route::get('{report}/export', [ReportController::class, 'export'])
             ->name('export')
             ->where('report', 'sales|sales-period|top-products|profit|stock');
    });

    // ── Manager+ routes ───────────────────────────────────────────
    Route::middleware('can:manage_products')->group(function () {
        Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
        Route::resource('products', ProductController::class);
    });

    Route::resource('categories', CategoryController::class)
         ->middleware('can:manage_categories');

    Route::resource('units', UnitController::class)->except('show')
         ->middleware('can:manage_units');

    Route::resource('discounts', DiscountController::class)->except('show')
         ->middleware('can:manage_discounts');

    Route::resource('suppliers', SupplierController::class)
         ->middleware('can:manage_suppliers');

    Route::prefix('purchases')->name('purchases.')->middleware('can:manage_purchases')->group(function () {
        Route::get('/',                [PurchaseController::class, 'index'])  ->name('index');
        Route::get('/create',          [PurchaseController::class, 'create']) ->name('create');
        Route::post('/',               [PurchaseController::class, 'store'])  ->name('store');
        Route::get('/{purchase}',      [PurchaseController::class, 'show'])   ->name('show');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])   ->name('edit');
        Route::put('/{purchase}',      [PurchaseController::class, 'update']) ->name('update');
        Route::delete('/{purchase}',   [PurchaseController::class, 'destroy'])->name('destroy');
        Route::patch('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('receive');
        Route::patch('/{purchase}/cancel',  [PurchaseController::class, 'cancel']) ->name('cancel');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('movements', [InventoryController::class, 'movements'])->name('movements');
        Route::middleware('can:adjust_stock')->group(function () {
            Route::get('adjustments/create', [InventoryController::class, 'adjustCreate'])->name('adjustments.create');
            Route::post('adjustments',       [InventoryController::class, 'adjustStore']) ->name('adjustments.store');
        });
    });

    // ── Admin-only routes ─────────────────────────────────────────
    Route::resource('taxes', TaxController::class)->except('show')
         ->middleware('can:manage_users'); // admin gate covers taxes too

    Route::resource('users', UserController::class)->except('show')
         ->middleware('can:manage_users');

    // ── Profile ───────────────────────────────────────────────────
    Route::get('/profile',          [ProfileController::class, 'edit'])          ->name('profile.edit');
    Route::patch('/profile',        [ProfileController::class, 'update'])        ->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

require __DIR__ . '/auth.php';
