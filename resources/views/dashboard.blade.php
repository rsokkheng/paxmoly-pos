@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Today's Sales</div>
        <div class="stat-value">${{ number_format($todaySales ?? 0, 2) }}</div>
        <div class="stat-sub">{{ $todayOrders ?? 0 }} transactions</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e">
        <div class="stat-label">Monthly Revenue</div>
        <div class="stat-value">${{ number_format($monthRevenue ?? 0, 2) }}</div>
        <div class="stat-sub">{{ $monthOrders ?? 0 }} orders this month</div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6">
        <div class="stat-label">Total Products</div>
        <div class="stat-value">{{ $totalProducts ?? 0 }}</div>
        <div class="stat-sub">{{ $lowStock ?? 0 }} low stock</div>
    </div>
    <div class="stat-card" style="--accent:#a78bfa">
        <div class="stat-label">Customers</div>
        <div class="stat-value">{{ $totalCustomers ?? 0 }}</div>
        <div class="stat-sub">{{ $newCustomers ?? 0 }} new this month</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 340px; gap:16px;">
    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Sales</span>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#Invoice</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales ?? [] as $sale)
                    <tr>
                        <td><span class="td-mono">{{ $sale->invoice_no }}</span></td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $sale->items_count }}</td>
                        <td class="mono">${{ number_format($sale->total, 2) }}</td>
                        <td><span class="badge badge-success">Paid</span></td>
                        <td class="td-mono">{{ $sale->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:32px;">No sales yet today</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Low Stock Alert</span>
            <span class="badge badge-danger">{{ $lowStockCount ?? 0 }}</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @forelse($lowStockProducts ?? [] as $product)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; background:var(--bg); border-radius:var(--radius); border:1px solid var(--border);">
                <div>
                    <div style="font-size:13px; font-weight:500;">{{ $product->name }}</div>
                    <div class="td-mono">SKU: {{ $product->sku }}</div>
                </div>
                <span class="badge badge-danger">{{ $product->stock }} left</span>
            </div>
            @empty
            <div style="text-align:center; color:var(--muted); padding:24px; font-size:13px;">
                <i class="fas fa-check-circle" style="font-size:20px; color:var(--success); display:block; margin-bottom:8px;"></i>
                All stock levels OK
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
