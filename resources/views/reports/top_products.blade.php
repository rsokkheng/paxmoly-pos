@extends('layouts.app')
@section('title', 'Top Products')
@section('page-title', 'Top Products Report')
@section('breadcrumb', 'Reports / Top Products')

@section('topbar-actions')
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.top_products') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:160px;">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:150px;">
            <label class="form-label">Sort By</label>
            <select name="sort" class="form-control">
                <option value="revenue" {{ $sortBy === 'revenue' ? 'selected' : '' }}>Revenue</option>
                <option value="qty"     {{ $sortBy === 'qty'     ? 'selected' : '' }}>Qty Sold</option>
                <option value="orders"  {{ $sortBy === 'orders'  ? 'selected' : '' }}>Order Count</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:100px;">
            <label class="form-label">Show Top</label>
            <select name="limit" class="form-control">
                <option value="10"  {{ $limit == 10  ? 'selected' : '' }}>Top 10</option>
                <option value="20"  {{ $limit == 20  ? 'selected' : '' }}>Top 20</option>
                <option value="50"  {{ $limit == 50  ? 'selected' : '' }}>Top 50</option>
                <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Top 100</option>
            </select>
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ route('reports.top_products') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary -->
@php
    $totalRevenue = $products->sum('revenue');
    $totalQty     = $products->sum('qty_sold');
    $totalProfit  = $products->sum('profit');
    $totalCogs    = $products->sum('cogs');
    $overallMargin = $totalRevenue > 0 ? round($totalProfit / $totalRevenue * 100, 1) : 0;
@endphp

<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
        <div class="stat-sub">top {{ $products->count() }} products</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Total Profit</div>
        <div class="stat-value">${{ number_format($totalProfit, 2) }}</div>
        <div class="stat-sub">after cost of goods</div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6;">
        <div class="stat-label">Units Sold</div>
        <div class="stat-value">{{ number_format($totalQty) }}</div>
        <div class="stat-sub">across all products</div>
    </div>
    <div class="stat-card" style="--accent:#a855f7;">
        <div class="stat-label">Avg Margin</div>
        <div class="stat-value">{{ $overallMargin }}%</div>
        <div class="stat-sub">blended profit margin</div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Top {{ $products->count() }} Products</span>
        <span class="badge badge-info">sorted by {{ $sortBy }}</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Orders</th>
                    <th>Qty Sold</th>
                    <th>Revenue</th>
                    <th>COGS</th>
                    <th>Profit</th>
                    <th>Margin</th>
                    <th>Rev Share</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; $maxRev = $products->max('revenue') ?: 1; @endphp
                @forelse($products as $item)
                <tr>
                    <td>
                        @if($i <= 3)
                            <span class="mono" style="color:var(--accent);font-weight:700;">{{ $i++ }}</span>
                        @else
                            <span class="td-mono">{{ $i++ }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $item->product->name ?? '—' }}</div>
                        @if($item->product?->code)
                            <div class="td-mono">{{ $item->product->code }}</div>
                        @endif
                    </td>
                    <td>
                        @if($item->product?->category)
                            <span class="badge badge-muted">{{ $item->product->category->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="mono">{{ number_format($item->order_count) }}</td>
                    <td class="mono">{{ number_format($item->qty_sold) }}</td>
                    <td class="mono" style="font-weight:600;">${{ number_format($item->revenue, 2) }}</td>
                    <td class="mono text-muted">${{ number_format($item->cogs, 2) }}</td>
                    <td class="mono {{ $item->profit >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($item->profit, 2) }}</td>
                    <td>
                        <span class="badge {{ $item->margin >= 30 ? 'badge-success' : ($item->margin >= 10 ? 'badge-warn' : 'badge-danger') }}">
                            {{ $item->margin }}%
                        </span>
                    </td>
                    <td>
                        @php $share = $totalRevenue > 0 ? round($item->revenue / $totalRevenue * 100, 1) : 0; @endphp
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="width:60px;background:var(--surface2);border-radius:3px;height:5px;">
                                <div style="width:{{ min($item->revenue / $maxRev * 100, 100) }}%;background:var(--accent);height:5px;border-radius:3px;"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);">{{ $share }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:40px;">No sales data for this period.</td></tr>
                @endforelse
            </tbody>
            @if($products->count())
            <tfoot>
                <tr style="background:var(--surface2);font-weight:600;">
                    <td colspan="3" class="mono" style="padding:11px 14px;">TOTAL</td>
                    <td class="mono">—</td>
                    <td class="mono">{{ number_format($totalQty) }}</td>
                    <td class="mono" style="color:var(--accent);">${{ number_format($totalRevenue, 2) }}</td>
                    <td class="mono">${{ number_format($totalCogs, 2) }}</td>
                    <td class="mono text-success">${{ number_format($totalProfit, 2) }}</td>
                    <td><span class="badge badge-info">{{ $overallMargin }}%</span></td>
                    <td class="mono">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .card:first-child, .btn { display: none !important; }
    .main { margin: 0; }
    .content { padding: 0; }
    body { background: #fff; color: #000; }
    .card { border: 1px solid #ccc; }
    .stat-card::after { display: none; }
}
</style>
@endpush
