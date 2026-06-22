@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── Out-of-Stock Banner ─────────────────────────────────────────── --}}
@if(($outOfStockCount ?? 0) > 0)
<div style="background:rgba(239,68,68,.1);border:1.5px solid rgba(239,68,68,.35);border-radius:var(--radius);padding:14px 18px;margin-bottom:16px;display:flex;align-items:flex-start;gap:14px;">
    <i class="fas fa-exclamation-triangle" style="color:#ef4444;font-size:20px;margin-top:2px;flex-shrink:0;"></i>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:700;font-size:14px;color:#ef4444;margin-bottom:6px;">
            {{ $outOfStockCount }} Product{{ $outOfStockCount !== 1 ? 's' : '' }} Out of Stock
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach($outOfStockProducts as $p)
            <span style="font-size:11px;background:rgba(239,68,68,.15);color:#ef4444;border:1px solid rgba(239,68,68,.3);border-radius:4px;padding:2px 8px;font-family:var(--mono);">
                {{ $p->name_en }}
            </span>
            @endforeach
        </div>
    </div>
    <a href="{{ route('reports.stock') }}?stock_status=out" class="btn btn-secondary" style="font-size:12px;padding:5px 12px;flex-shrink:0;">
        View All
    </a>
</div>
@endif

{{-- ── Stat Cards ──────────────────────────────────────────────────── --}}
<div class="stats-grid" style="margin-bottom:16px;">
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
        <div class="stat-sub">
            @if(($outOfStockCount ?? 0) > 0)
                <span style="color:#ef4444;font-weight:600;">{{ $outOfStockCount }} out of stock</span>
            @elseif(($lowStockCount ?? 0) > 0)
                <span style="color:var(--accent);">{{ $lowStockCount }} low stock</span>
            @else
                All stock levels OK
            @endif
        </div>
    </div>
    <div class="stat-card" style="--accent:#a78bfa">
        <div class="stat-label">Customers</div>
        <div class="stat-value">{{ $totalCustomers ?? 0 }}</div>
        <div class="stat-sub">{{ $newCustomers ?? 0 }} new this month</div>
    </div>
</div>

{{-- ── Main Content Grid ───────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">

    {{-- Recent Sales --}}
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
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales ?? [] as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}"
                               class="td-mono" style="color:var(--accent);text-decoration:none;">
                                {{ $sale->invoice_no }}
                            </a>
                        </td>
                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $sale->items_count }}</td>
                        <td class="mono" style="font-weight:600;">${{ number_format($sale->grand_total, 2) }}</td>
                        <td class="td-mono">{{ $sale->created_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:var(--muted);padding:32px;">
                            No sales today
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock Alerts --}}
    <div style="display:flex;flex-direction:column;gap:12px;">

        {{-- Out of Stock --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title" style="display:flex;align-items:center;gap:7px;">
                    <i class="fas fa-times-circle" style="color:#ef4444;font-size:13px;"></i>
                    Out of Stock
                </span>
                <span class="badge badge-danger">{{ $outOfStockCount ?? 0 }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;max-height:220px;overflow-y:auto;">
                @forelse($outOfStockProducts ?? [] as $product)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:rgba(239,68,68,.06);border-radius:var(--radius);border:1px solid rgba(239,68,68,.2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $product->name_en }}
                        </div>
                        <div class="td-mono" style="font-size:10px;color:var(--muted);">{{ $product->code }}</div>
                    </div>
                    <span class="badge badge-danger" style="flex-shrink:0;margin-left:8px;">OUT</span>
                </div>
                @empty
                <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">
                    <i class="fas fa-check-circle" style="font-size:18px;color:var(--success);display:block;margin-bottom:6px;"></i>
                    No out-of-stock items
                </div>
                @endforelse
            </div>
            @if(($outOfStockCount ?? 0) > 0)
            <div style="padding-top:10px;border-top:1px solid var(--border);margin-top:6px;">
                <a href="{{ route('reports.stock') }}?stock_status=out"
                   class="btn btn-secondary" style="width:100%;justify-content:center;font-size:12px;">
                    <i class="fas fa-arrow-right"></i> Manage Stock
                </a>
            </div>
            @endif
        </div>

        {{-- Low Stock --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title" style="display:flex;align-items:center;gap:7px;">
                    <i class="fas fa-exclamation-circle" style="color:var(--accent);font-size:13px;"></i>
                    Low Stock
                </span>
                <span class="badge badge-warn">{{ $lowStockCount ?? 0 }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;max-height:220px;overflow-y:auto;">
                @forelse($lowStockProducts ?? [] as $product)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:rgba(240,180,41,.06);border-radius:var(--radius);border:1px solid rgba(240,180,41,.2);">
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $product->name_en }}
                        </div>
                        <div class="td-mono" style="font-size:10px;color:var(--muted);">{{ $product->code }}</div>
                    </div>
                    <span class="badge badge-warn" style="flex-shrink:0;margin-left:8px;">
                        {{ $product->stock_quantity }} left
                    </span>
                </div>
                @empty
                <div style="text-align:center;color:var(--muted);padding:20px;font-size:13px;">
                    <i class="fas fa-check-circle" style="font-size:18px;color:var(--success);display:block;margin-bottom:6px;"></i>
                    All stock levels OK
                </div>
                @endforelse
            </div>
            @if(($lowStockCount ?? 0) > 0)
            <div style="padding-top:10px;border-top:1px solid var(--border);margin-top:6px;">
                <a href="{{ route('reports.stock') }}?stock_status=low"
                   class="btn btn-secondary" style="width:100%;justify-content:center;font-size:12px;">
                    <i class="fas fa-arrow-right"></i> View Low Stock
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
