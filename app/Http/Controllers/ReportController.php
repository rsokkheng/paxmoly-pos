<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Exports\ReportExport;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


// ─────────────────────────────────────────────────────────────────
// FILE: app/Http/Controllers/ReportController.php
// ─────────────────────────────────────────────────────────────────
class ReportController extends Controller
{
    public function sales(Request $request) {
        $dateFrom      = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $dateTo        = $request->input('to')   ?? now()->toDateString();
        $paymentMethod = $request->input('payment_method');

        $summary = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(grand_total) as total_revenue,
                SUM(discount_amount) as total_discounts,
                SUM(tax_amount) as total_taxes,
                AVG(grand_total) as avg_sale
            ')
            ->first();

        $totalOrders   = (int) ($summary->total_sales ?? 0);
        $totalRevenue  = (float) ($summary->total_revenue ?? 0);
        $totalDiscount = (float) ($summary->total_discounts ?? 0);

        $salesQuery = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->withCount('items');

        if ($paymentMethod) {
            $salesQuery->where('payment_method', $paymentMethod);
        }

        $sales = $salesQuery->with('customer')->latest()->paginate(20);

        $dailySales = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(grand_total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = SaleItem::whereHas('sale', fn($q) =>
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                  ->where('status', 'completed'))
            ->selectRaw('product_id, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('reports.sales', compact(
            'summary', 'dailySales', 'topProducts', 'dateFrom', 'dateTo',
            'totalOrders', 'totalRevenue', 'totalDiscount', 'sales'
        ));
    }

    public function salesByPeriod(Request $request) {
        $period   = \in_array($request->input('period'), ['daily', 'weekly', 'monthly']) ? $request->input('period') : 'daily';
        $dateFrom = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->input('to')   ?? now()->toDateString();

        $groupExpr = match($period) {
            'weekly'  => "YEARWEEK(created_at, 1)",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            default   => "DATE(created_at)",
        };

        $rows = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->selectRaw("
                {$groupExpr} as period_key,
                MIN(DATE(created_at)) as period_start,
                COUNT(*) as total_orders,
                SUM(grand_total) as total_revenue,
                SUM(discount_amount) as total_discounts,
                SUM(tax_amount) as total_taxes,
                AVG(grand_total) as avg_order
            ")
            ->groupBy(DB::raw($groupExpr))
            ->orderBy(DB::raw($groupExpr))
            ->get();

        $summary = [
            'total_orders'  => $rows->sum('total_orders'),
            'total_revenue' => $rows->sum('total_revenue'),
            'avg_order'     => $rows->count() ? $rows->avg('avg_order') : 0,
        ];

        return view('reports.sales_period', compact('rows', 'summary', 'period', 'dateFrom', 'dateTo'));
    }

    public function topProducts(Request $request) {
        $dateFrom   = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $dateTo     = $request->input('to')   ?? now()->toDateString();
        $sortBy     = \in_array($request->input('sort'), ['revenue', 'qty', 'orders']) ? $request->input('sort') : 'revenue';
        $limit      = min((int) ($request->input('limit') ?? 20), 100);
        $categoryId = $request->input('category_id');

        $query = SaleItem::whereHas('sale', fn($q) =>
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                  ->where('status', 'completed'))
            ->selectRaw('product_id, SUM(quantity) as qty_sold, SUM(subtotal) as revenue, COUNT(DISTINCT sale_id) as order_count')
            ->groupBy('product_id')
            ->with('product.category');

        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }

        $orderCol = match($sortBy) {
            'qty'    => 'qty_sold',
            'orders' => 'order_count',
            default  => 'revenue',
        };

        $products = $query->orderByDesc($orderCol)->limit($limit)->get()
            ->map(function ($item) {
                $item->cogs   = $item->product ? (float) $item->product->buying_price * $item->qty_sold : 0;
                $item->profit = (float) $item->revenue - $item->cogs;
                $item->margin = $item->revenue > 0 ? round($item->profit / $item->revenue * 100, 1) : 0;
                return $item;
            });

        $categories = Category::orderBy('name')->get();

        return view('reports.top_products', compact('products', 'categories', 'dateFrom', 'dateTo', 'sortBy', 'limit'));
    }

    public function profit(Request $request) {
        $dateFrom = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->input('to')   ?? now()->toDateString();
        $groupBy  = $request->input('group_by') === 'category' ? 'category' : 'product';

        $base = SaleItem::whereHas('sale', fn($q) =>
            $q->whereBetween(DB::raw('DATE(sales.created_at)'), [$dateFrom, $dateTo])
              ->where('status', 'completed'))
            ->join('products', 'sale_items.product_id', '=', 'products.id');

        if ($groupBy === 'category') {
            $rows = (clone $base)
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->selectRaw('
                    categories.id   as group_id,
                    categories.name as group_name,
                    NULL            as product_code,
                    SUM(sale_items.quantity) as qty_sold,
                    SUM(sale_items.subtotal) as revenue,
                    SUM(products.buying_price * sale_items.quantity) as cogs
                ')
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('revenue')
                ->get();
        } else {
            $rows = (clone $base)
                ->selectRaw('
                    products.id   as group_id,
                    products.name as group_name,
                    products.code as product_code,
                    SUM(sale_items.quantity) as qty_sold,
                    SUM(sale_items.subtotal) as revenue,
                    SUM(products.buying_price * sale_items.quantity) as cogs
                ')
                ->groupBy('products.id', 'products.name', 'products.code')
                ->orderByDesc('revenue')
                ->get();
        }

        $rows = $rows->map(function ($r) {
            $r->profit = (float) $r->revenue - (float) $r->cogs;
            $r->margin = $r->revenue > 0 ? round($r->profit / $r->revenue * 100, 1) : 0;
            return $r;
        });

        $totals = [
            'revenue' => $rows->sum('revenue'),
            'cogs'    => $rows->sum('cogs'),
            'profit'  => $rows->sum('profit'),
        ];
        $totals['margin'] = $totals['revenue'] > 0
            ? round($totals['profit'] / $totals['revenue'] * 100, 1)
            : 0;

        return view('reports.profit', compact('rows', 'totals', 'dateFrom', 'dateTo', 'groupBy'));
    }

    public function stock(Request $request) {
        $categoryId  = $request->input('category_id');
        $stockStatus = $request->input('stock_status');
        $query = Product::where('is_active', true)->with(['category', 'unit']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'out') {
            $query->where('stock_quantity', '<=', 0);
        } elseif ($stockStatus === 'low') {
            $query->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'alert_quantity');
        } elseif ($stockStatus === 'ok') {
            $query->whereColumn('stock_quantity', '>', 'alert_quantity');
        }

        $products = $query->orderBy('name')->paginate(20);

        $categories   = Category::orderBy('name')->get();
        $totalProducts = Product::where('is_active', true)->count();
        $outOfStock   = Product::where('is_active', true)->where('stock_quantity', '<=', 0)->count();
        $lowStock     = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->count();

        $totalValue = (float) Product::where('is_active', true)
            ->selectRaw('SUM(stock_quantity * buying_price) as total_cost')
            ->value('total_cost');

        return view('reports.stock', compact('products', 'categories', 'totalProducts', 'outOfStock', 'lowStock', 'totalValue'));
    }

    public function export(Request $request, string $report)
    {
        $reportKey = str_replace('-', '_', $report);
        $filename  = "{$reportKey}-" . now()->format('Ymd_His') . '.xlsx';

        switch ($reportKey) {
            case 'sales':
                $dateFrom      = $request->input('from') ?? now()->startOfMonth()->toDateString();
                $dateTo        = $request->input('to')   ?? now()->toDateString();
                $paymentMethod = $request->input('payment_method');

                $salesQuery = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->withCount('items')
                    ->with('customer')
                    ->latest();

                if ($paymentMethod) {
                    $salesQuery->where('payment_method', $paymentMethod);
                }

                $sales = $salesQuery->get();
                $headings = ['Invoice', 'Customer', 'Items', 'Subtotal', 'Discount', 'Tax', 'Total', 'Payment Method', 'Date'];
                $rows = $sales->map(function ($sale) {
                    return [
                        $sale->invoice_no,
                        $sale->customer->name ?? 'Walk-in',
                        $sale->items_count,
                        (float) $sale->subtotal,
                        (float) $sale->discount_amount,
                        (float) $sale->tax_amount,
                        (float) $sale->grand_total,
                        ucfirst($sale->payment_method ?? 'cash'),
                        $sale->created_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray();
                break;

            case 'sales_period':
                $period   = in_array($request->input('period'), ['daily', 'weekly', 'monthly']) ? $request->input('period') : 'daily';
                $dateFrom = $request->input('from') ?? now()->startOfMonth()->toDateString();
                $dateTo   = $request->input('to')   ?? now()->toDateString();

                $groupExpr = match ($period) {
                    'weekly'  => "YEARWEEK(created_at, 1)",
                    'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
                    default   => "DATE(created_at)",
                };

                $rows = Sale::whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                    ->where('status', 'completed')
                    ->selectRaw(
                        "{$groupExpr} as period_key, MIN(DATE(created_at)) as period_start, COUNT(*) as total_orders, SUM(grand_total) as total_revenue, SUM(discount_amount) as total_discounts, SUM(tax_amount) as total_taxes, AVG(grand_total) as avg_order"
                    )
                    ->groupBy(DB::raw($groupExpr))
                    ->orderBy(DB::raw($groupExpr))
                    ->get();

                $headings = ['Period', 'Orders', 'Revenue', 'Discounts', 'Taxes', 'Avg Order'];
                $rows = $rows->map(function ($row) use ($period) {
                    $date = \Carbon\Carbon::parse($row->period_start);
                    $periodLabel = match ($period) {
                        'monthly' => $date->format('F Y'),
                        'weekly'  => 'Week of ' . $date->startOfWeek()->format('M d') . ' - ' . $date->copy()->endOfWeek()->format('M d, Y'),
                        default   => $date->format('Y-m-d'),
                    };
                    return [
                        $periodLabel,
                        (int) $row->total_orders,
                        (float) $row->total_revenue,
                        (float) $row->total_discounts,
                        (float) $row->total_taxes,
                        (float) $row->avg_order,
                    ];
                })->toArray();
                break;

            case 'top_products':
                $dateFrom   = $request->input('from') ?? now()->startOfMonth()->toDateString();
                $dateTo     = $request->input('to')   ?? now()->toDateString();
                $sortBy     = in_array($request->input('sort'), ['revenue', 'qty', 'orders']) ? $request->input('sort') : 'revenue';
                $limit      = min((int) ($request->input('limit') ?? 20), 100);
                $categoryId = $request->input('category_id');

                $query = SaleItem::whereHas('sale', fn ($q) =>
                        $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
                          ->where('status', 'completed'))
                    ->selectRaw('product_id, SUM(quantity) as qty_sold, SUM(subtotal) as revenue, COUNT(DISTINCT sale_id) as order_count')
                    ->groupBy('product_id')
                    ->with('product.category');

                if ($categoryId) {
                    $query->whereHas('product', fn ($q) => $q->where('category_id', $categoryId));
                }

                $orderCol = match ($sortBy) {
                    'qty'    => 'qty_sold',
                    'orders' => 'order_count',
                    default  => 'revenue',
                };

                $items = $query->orderByDesc($orderCol)->limit($limit)->get()
                    ->map(function ($item) {
                        $item->cogs   = $item->product ? (float) $item->product->buying_price * $item->qty_sold : 0;
                        $item->profit = (float) $item->revenue - $item->cogs;
                        $item->margin = $item->revenue > 0 ? round($item->profit / $item->revenue * 100, 1) : 0;
                        return $item;
                    });

                $totalRevenue = $items->sum('revenue') ?: 1;
                $headings = ['Product', 'SKU', 'Category', 'Orders', 'Qty Sold', 'Revenue', 'COGS', 'Profit', 'Margin %', 'Revenue Share %'];
                $rows = $items->map(function ($item) use ($totalRevenue) {
                    return [
                        $item->product->name ?? '—',
                        $item->product->code ?? '—',
                        $item->product->category->name ?? '—',
                        (int) $item->order_count,
                        (int) $item->qty_sold,
                        (float) $item->revenue,
                        (float) $item->cogs,
                        (float) $item->profit,
                        $item->margin,
                        round($item->revenue / $totalRevenue * 100, 1),
                    ];
                })->toArray();
                break;

            case 'profit':
                $dateFrom = $request->input('from') ?? now()->startOfMonth()->toDateString();
                $dateTo   = $request->input('to')   ?? now()->toDateString();
                $groupBy  = $request->input('group_by') === 'category' ? 'category' : 'product';

                $base = SaleItem::whereHas('sale', fn ($q) =>
                    $q->whereBetween(DB::raw('DATE(sales.created_at)'), [$dateFrom, $dateTo])
                      ->where('status', 'completed'))
                    ->join('products', 'sale_items.product_id', '=', 'products.id');

                if ($groupBy === 'category') {
                    $rows = (clone $base)
                        ->join('categories', 'products.category_id', '=', 'categories.id')
                        ->selectRaw('categories.name as group_name, SUM(sale_items.quantity) as qty_sold, SUM(sale_items.subtotal) as revenue, SUM(products.buying_price * sale_items.quantity) as cogs')
                        ->groupBy('categories.id', 'categories.name')
                        ->orderByDesc('revenue')
                        ->get();
                } else {
                    $rows = (clone $base)
                        ->selectRaw('products.name as group_name, products.code as product_code, SUM(sale_items.quantity) as qty_sold, SUM(sale_items.subtotal) as revenue, SUM(products.buying_price * sale_items.quantity) as cogs')
                        ->groupBy('products.id', 'products.name', 'products.code')
                        ->orderByDesc('revenue')
                        ->get();
                }

                $rows = $rows->map(function ($row) {
                    $row->profit = (float) $row->revenue - (float) $row->cogs;
                    $row->margin = $row->revenue > 0 ? round($row->profit / $row->revenue * 100, 1) : 0;
                    return $row;
                });

                if ($groupBy === 'category') {
                    $headings = ['Category', 'Qty Sold', 'Revenue', 'COGS', 'Profit', 'Margin %'];
                    $rows = $rows->map(fn ($row) => [
                        $row->group_name,
                        (int) $row->qty_sold,
                        (float) $row->revenue,
                        (float) $row->cogs,
                        (float) $row->profit,
                        $row->margin,
                    ])->toArray();
                } else {
                    $headings = ['Product', 'SKU', 'Qty Sold', 'Revenue', 'COGS', 'Profit', 'Margin %'];
                    $rows = $rows->map(fn ($row) => [
                        $row->group_name,
                        $row->product_code,
                        (int) $row->qty_sold,
                        (float) $row->revenue,
                        (float) $row->cogs,
                        (float) $row->profit,
                        $row->margin,
                    ])->toArray();
                }
                break;

            case 'stock':
                $categoryId  = $request->input('category_id');
                $stockStatus = $request->input('stock_status');
                $query = Product::where('is_active', true)->with(['category', 'unit']);

                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }

                if ($stockStatus === 'out') {
                    $query->where('stock_quantity', '<=', 0);
                } elseif ($stockStatus === 'low') {
                    $query->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'alert_quantity');
                } elseif ($stockStatus === 'ok') {
                    $query->whereColumn('stock_quantity', '>', 'alert_quantity');
                }

                $products = $query->orderBy('name')->get();
                $headings = ['Product', 'SKU', 'Category', 'Unit', 'Cost Price', 'Sell Price', 'In Stock', 'Min Stock', 'Stock Value', 'Status'];
                $rows = $products->map(function ($product) {
                    $status = $product->stock_quantity <= 0 ? 'Out of Stock' : ($product->stock_quantity <= ($product->alert_quantity ?? 5) ? 'Low Stock' : 'In Stock');
                    return [
                        $product->name,
                        $product->code,
                        $product->category->name ?? '—',
                        $product->unit->short_name ?? '—',
                        (float) $product->buying_price,
                        (float) $product->selling_price,
                        (int) $product->stock_quantity,
                        (int) ($product->alert_quantity ?? 0),
                        (float) ($product->stock_quantity * $product->buying_price),
                        $status,
                    ];
                })->toArray();
                break;

            default:
                abort(404);
        }

        return Excel::download(new ReportExport($rows, $headings), $filename);
    }
}
