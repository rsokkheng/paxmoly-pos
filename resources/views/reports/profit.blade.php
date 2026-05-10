@extends('layouts.app')
@section('title', 'Profit & Margin Report')
@section('page-title', 'Profit & Margin Report')
@section('breadcrumb', 'Reports / Profit & Margin')

@section('topbar-actions')
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.profit') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="form-group" style="margin:0;min-width:180px;">
            <label class="form-label">Group By</label>
            <select name="group_by" class="form-control">
                <option value="product"  {{ $groupBy === 'product'  ? 'selected' : '' }}>Product</option>
                <option value="category" {{ $groupBy === 'category' ? 'selected' : '' }}>Category</option>
            </select>
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ route('reports.profit') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">${{ number_format($totals['revenue'], 2) }}</div>
        <div class="stat-sub">gross sales</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Cost of Goods</div>
        <div class="stat-value">${{ number_format($totals['cogs'], 2) }}</div>
        <div class="stat-sub">at buying price</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Gross Profit</div>
        <div class="stat-value">${{ number_format($totals['profit'], 2) }}</div>
        <div class="stat-sub">revenue minus COGS</div>
    </div>
    <div class="stat-card" style="--accent:#a855f7;">
        <div class="stat-label">Profit Margin</div>
        <div class="stat-value">{{ $totals['margin'] }}%</div>
        <div class="stat-sub">blended margin</div>
    </div>
</div>

<!-- Visual margin bar -->
<div class="card" style="margin-bottom:16px;padding:20px 24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span class="card-title">Revenue Breakdown</span>
        <span class="td-mono">{{ $dateFrom }} → {{ $dateTo }}</span>
    </div>
    <div style="height:28px;border-radius:6px;overflow:hidden;display:flex;background:var(--bg);">
        @php
            $cogsShare   = $totals['revenue'] > 0 ? $totals['cogs']   / $totals['revenue'] * 100 : 0;
            $profitShare = $totals['revenue'] > 0 ? $totals['profit']  / $totals['revenue'] * 100 : 0;
        @endphp
        <div style="width:{{ $cogsShare }}%;background:#ef4444;opacity:0.7;display:flex;align-items:center;justify-content:center;font-size:11px;font-family:var(--mono);color:#fff;white-space:nowrap;padding:0 4px;">
            @if($cogsShare > 8) COGS {{ round($cogsShare, 1) }}% @endif
        </div>
        <div style="width:{{ $profitShare }}%;background:#22c55e;opacity:0.8;display:flex;align-items:center;justify-content:center;font-size:11px;font-family:var(--mono);color:#fff;white-space:nowrap;padding:0 4px;">
            @if($profitShare > 8) Profit {{ round($profitShare, 1) }}% @endif
        </div>
    </div>
    <div style="display:flex;gap:20px;margin-top:10px;">
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);">
            <div style="width:10px;height:10px;background:#ef4444;border-radius:2px;opacity:0.7;"></div> Cost of Goods
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);">
            <div style="width:10px;height:10px;background:#22c55e;border-radius:2px;opacity:0.8;"></div> Gross Profit
        </div>
    </div>
</div>

<!-- Breakdown Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ $groupBy === 'category' ? 'By Category' : 'By Product' }}</span>
        <span class="badge badge-info">{{ $rows->count() }} {{ $groupBy === 'category' ? 'categories' : 'products' }}</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ $groupBy === 'category' ? 'Category' : 'Product' }}</th>
                    @if($groupBy === 'product')<th>Code</th>@endif
                    <th>Qty Sold</th>
                    <th>Revenue</th>
                    <th>COGS</th>
                    <th>Profit</th>
                    <th>Margin %</th>
                    <th>Profit Share</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; $totalProfit = $totals['profit'] ?: 1; @endphp
                @forelse($rows as $row)
                <tr>
                    <td class="td-mono">{{ $i++ }}</td>
                    <td style="font-weight:500;">{{ $row->group_name }}</td>
                    @if($groupBy === 'product')
                        <td class="td-mono">{{ $row->product_code ?? '—' }}</td>
                    @endif
                    <td class="mono">{{ number_format($row->qty_sold) }}</td>
                    <td class="mono">${{ number_format($row->revenue, 2) }}</td>
                    <td class="mono text-muted">${{ number_format($row->cogs, 2) }}</td>
                    <td class="mono {{ $row->profit >= 0 ? 'text-success' : 'text-danger' }}" style="font-weight:600;">
                        ${{ number_format($row->profit, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $row->margin >= 30 ? 'badge-success' : ($row->margin >= 10 ? 'badge-warn' : 'badge-danger') }}">
                            {{ $row->margin }}%
                        </span>
                    </td>
                    <td>
                        @php $share = round($row->profit / $totalProfit * 100, 1); @endphp
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="width:70px;background:var(--surface2);border-radius:3px;height:5px;">
                                <div style="width:{{ max(min($share, 100), 0) }}%;background:var(--success);height:5px;border-radius:3px;"></div>
                            </div>
                            <span style="font-size:11px;font-family:var(--mono);">{{ $share }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $groupBy === 'product' ? 9 : 8 }}" style="text-align:center;color:var(--muted);padding:40px;">
                        No sales data for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot>
                <tr style="background:var(--surface2);font-weight:600;">
                    <td colspan="{{ $groupBy === 'product' ? 3 : 2 }}" class="mono" style="padding:11px 14px;">TOTAL</td>
                    <td class="mono">{{ number_format($rows->sum('qty_sold')) }}</td>
                    <td class="mono" style="color:var(--accent);">${{ number_format($totals['revenue'], 2) }}</td>
                    <td class="mono">${{ number_format($totals['cogs'], 2) }}</td>
                    <td class="mono text-success">${{ number_format($totals['profit'], 2) }}</td>
                    <td><span class="badge badge-info">{{ $totals['margin'] }}%</span></td>
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
