@extends('layouts.app')
@section('title', 'Period Sales Report')
@section('page-title', 'Period Sales Report')
@section('breadcrumb', 'Reports / Period Sales')

@section('topbar-actions')
    <a href="{{ route('reports.export', ['report' => 'sales-period']) . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.sales_period') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="form-group" style="margin:0;min-width:160px;">
            <label class="form-label">Group By</label>
            <select name="period" class="form-control">
                <option value="daily"   {{ $period === 'daily'   ? 'selected' : '' }}>Daily</option>
                <option value="weekly"  {{ $period === 'weekly'  ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
            <a href="{{ route('reports.sales_period') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary -->
<div class="stats-grid" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">${{ number_format($summary['total_revenue'] ?? 0, 2) }}</div>
        <div class="stat-sub">{{ $period }} breakdown</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ number_format($summary['total_orders'] ?? 0) }}</div>
        <div class="stat-sub">completed transactions</div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6;">
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-value">${{ number_format($summary['avg_order'] ?? 0, 2) }}</div>
        <div class="stat-sub">per transaction</div>
    </div>
    <div class="stat-card" style="--accent:#a855f7;">
        <div class="stat-label">Periods</div>
        <div class="stat-value">{{ $rows->count() }}</div>
        <div class="stat-sub">{{ $period }} buckets</div>
    </div>
</div>

<!-- Period Breakdown Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">{{ ucfirst($period) }} Breakdown</span>
        <span class="badge badge-info">{{ $rows->count() }} periods</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th>Orders</th>
                    <th>Revenue</th>
                    <th>Discounts</th>
                    <th>Taxes</th>
                    <th>Avg Order</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalRev = $summary['total_revenue'] ?: 1; $i = 1; @endphp
                @forelse($rows as $row)
                <tr>
                    <td class="td-mono">{{ $i++ }}</td>
                    <td style="font-weight:500;">
                        @php
                            $date = \Carbon\Carbon::parse($row->period_start);
                        @endphp
                        @if($period === 'monthly')
                            {{ $date->format('F Y') }}
                        @elseif($period === 'weekly')
                            Week of {{ $date->startOfWeek()->format('M d') }} – {{ $date->copy()->endOfWeek()->format('M d, Y') }}
                        @else
                            {{ $date->format('D, M d Y') }}
                        @endif
                    </td>
                    <td class="mono">{{ number_format($row->total_orders) }}</td>
                    <td class="mono" style="font-weight:600;color:var(--accent);">${{ number_format($row->total_revenue, 2) }}</td>
                    <td class="mono text-danger">-${{ number_format($row->total_discounts, 2) }}</td>
                    <td class="mono">${{ number_format($row->total_taxes, 2) }}</td>
                    <td class="mono">${{ number_format($row->avg_order, 2) }}</td>
                    <td>
                        @php $pct = round($row->total_revenue / $totalRev * 100, 1); @endphp
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:var(--surface2);border-radius:3px;height:6px;min-width:60px;">
                                <div style="width:{{ $pct }}%;background:var(--accent);height:6px;border-radius:3px;"></div>
                            </div>
                            <span class="mono" style="font-size:11px;white-space:nowrap;">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:40px;">No sales data for this period.</td></tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot>
                <tr style="background:var(--surface2);font-weight:600;">
                    <td colspan="2" class="mono" style="padding:11px 14px;">TOTAL</td>
                    <td class="mono">{{ number_format($summary['total_orders']) }}</td>
                    <td class="mono" style="color:var(--accent);">${{ number_format($summary['total_revenue'], 2) }}</td>
                    <td class="mono text-danger">-${{ number_format($rows->sum('total_discounts'), 2) }}</td>
                    <td class="mono">${{ number_format($rows->sum('total_taxes'), 2) }}</td>
                    <td class="mono">${{ number_format($summary['avg_order'], 2) }}</td>
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
