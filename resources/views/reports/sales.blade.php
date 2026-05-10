@extends('layouts.app')
@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('breadcrumb', 'Reports / Sales')

@section('topbar-actions')
    <a href="{{ route('reports.export', ['report' => 'sales']) . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.sales') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from', now()->startOfMonth()->toDateString()) }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to', now()->toDateString()) }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Payment</label>
            <select name="payment_method" class="form-control">
                <option value="">All Methods</option>
                <option value="cash" {{ request('payment_method')=='cash'?'selected':'' }}>Cash</option>
                <option value="card" {{ request('payment_method')=='card'?'selected':'' }}>Card</option>
                <option value="transfer" {{ request('payment_method')=='transfer'?'selected':'' }}>Transfer</option>
            </select>
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('reports.sales') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">Total Sales</div>
        <div class="stat-value">${{ number_format($totalRevenue ?? 0, 2) }}</div>
        <div class="stat-sub">{{ $totalOrders ?? 0 }} transactions</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Net Revenue</div>
        <div class="stat-value">${{ number_format(($totalRevenue ?? 0) - ($totalDiscount ?? 0), 2) }}</div>
        <div class="stat-sub">after discounts</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Total Discounts</div>
        <div class="stat-value">${{ number_format($totalDiscount ?? 0, 2) }}</div>
        <div class="stat-sub">across all sales</div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6;">
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-value">${{ $totalOrders ? number_format(($totalRevenue ?? 0) / $totalOrders, 2) : '0.00' }}</div>
        <div class="stat-sub">per transaction</div>
    </div>
</div>

<!-- Sales Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Sales Transactions</span>
        <span class="badge badge-info">{{ $sales->total() }} records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#Invoice</th><th>Customer</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Total</th><th>Payment</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}" class="td-mono" style="color:var(--accent);text-decoration:none;">{{ $sale->invoice_no }}</a></td>
                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->items_count ?? '—' }}</td>
                    <td class="mono">${{ number_format($sale->subtotal, 2) }}</td>
                    <td class="mono text-danger">-${{ number_format($sale->discount_amount, 2) }}</td>
                    <td class="mono">${{ number_format($sale->tax_amount, 2) }}</td>
                    <td class="mono" style="font-weight:600;">${{ number_format($sale->total, 2) }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($sale->payment_method ?? 'cash') }}</span></td>
                    <td class="td-mono">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:40px;">No sales in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $sales->withQueryString()->links() }}</div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .card:first-child, .pagination { display: none !important; }
    .main { margin: 0; }
    .content { padding: 0; }
    body { background: #fff; color: #000; }
    .card { border: 1px solid #ccc; }
    .stat-card::after { display: none; }
}
</style>
@endpush
