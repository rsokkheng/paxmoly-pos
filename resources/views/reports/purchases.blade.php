@extends('layouts.app')
@section('title', 'Purchase Report')
@section('page-title', 'Purchase Report')
@section('breadcrumb', 'Reports / Purchases')

@section('topbar-actions')
    <a href="{{ route('reports.export', ['report' => 'purchases']) . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}"
       class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

{{-- Filters --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.purchases') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="form-group" style="margin:0;flex:2;min-width:180px;">
            <label class="form-label">Supplier</label>
            <select name="supplier_id" class="form-control">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="received"  {{ $status === 'received'  ? 'selected' : '' }}>Received</option>
                <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Summary Stats --}}
<div class="stats-grid" style="grid-template-columns:repeat(6,1fr);margin-bottom:16px;">
    <div class="stat-card" style="--accent:#3b82f6;">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ number_format($summary->total_orders ?? 0) }}</div>
        <div class="stat-sub">purchase orders</div>
    </div>
    <div class="stat-card" style="--accent:#8b5cf6;">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value">${{ number_format($summary->total_amount ?? 0, 2) }}</div>
        <div class="stat-sub">grand total</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">${{ number_format($summary->total_paid ?? 0, 2) }}</div>
        <div class="stat-sub">amount paid</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Total Due</div>
        <div class="stat-value">${{ number_format($summary->total_due ?? 0, 2) }}</div>
        <div class="stat-sub">outstanding</div>
    </div>
    <div class="stat-card" style="--accent:#10b981;">
        <div class="stat-label">Received</div>
        <div class="stat-value">{{ number_format($summary->total_received ?? 0) }}</div>
        <div class="stat-sub">stock updated</div>
    </div>
    <div class="stat-card" style="--accent:#f59e0b;">
        <div class="stat-label">Pending</div>
        <div class="stat-value">{{ number_format($summary->total_pending ?? 0) }}</div>
        <div class="stat-sub">awaiting receipt</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

    {{-- Daily Trend --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Daily Purchase Trend</span>
            <span class="badge badge-info">{{ $daily->count() }} days</span>
        </div>
        @if($daily->count())
        @php $maxAmt = $daily->max('amount') ?: 1; @endphp
        <div style="padding:0 16px 16px;">
            @foreach($daily as $d)
            @php $pct = round($d->amount / $maxAmt * 100); @endphp
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <div style="width:80px;font-size:11px;color:var(--muted);font-family:var(--mono);flex-shrink:0;">
                    {{ \Carbon\Carbon::parse($d->date)->format('M d') }}
                </div>
                <div style="flex:1;background:var(--surface2);border-radius:4px;height:20px;position:relative;overflow:hidden;">
                    <div style="width:{{ $pct }}%;background:var(--accent);height:100%;border-radius:4px;transition:width .3s;"></div>
                </div>
                <div style="width:80px;text-align:right;font-size:11px;font-family:var(--mono);color:var(--text);">
                    ${{ number_format($d->amount, 0) }}
                </div>
                <div style="width:28px;text-align:right;font-size:10px;color:var(--muted);">
                    {{ $d->count }}
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center;color:var(--muted);padding:40px;font-size:13px;">No data for this period.</div>
        @endif
    </div>

    {{-- Top Suppliers --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Top Suppliers</span>
        </div>
        @if($topSuppliers->count())
        @php $maxSup = $topSuppliers->max('total_amount') ?: 1; @endphp
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier</th>
                        <th style="text-align:right;">Orders</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">Paid</th>
                        <th style="text-align:right;">Due</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSuppliers as $i => $sup)
                    @php $due = max(0, $sup->total_amount - $sup->paid_amount); @endphp
                    <tr>
                        <td class="td-mono" style="color:var(--muted);">{{ $i + 1 }}</td>
                        <td style="font-weight:500;">{{ $sup->name }}</td>
                        <td class="mono" style="text-align:right;">{{ $sup->order_count }}</td>
                        <td class="mono" style="text-align:right;font-weight:600;">${{ number_format($sup->total_amount, 2) }}</td>
                        <td class="mono" style="text-align:right;color:var(--success);">${{ number_format($sup->paid_amount, 2) }}</td>
                        <td class="mono" style="text-align:right;color:{{ $due > 0 ? 'var(--danger)' : 'var(--muted)' }};">
                            {{ $due > 0 ? '$'.number_format($due, 2) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align:center;color:var(--muted);padding:40px;font-size:13px;">No suppliers for this period.</div>
        @endif
    </div>
</div>

{{-- Purchase List --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Purchase Orders</span>
        <span class="badge badge-info">{{ $purchases->total() }} records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align:right;">Grand Total</th>
                    <th style="text-align:right;">Paid</th>
                    <th style="text-align:right;">Due</th>
                    <th>Created By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                @php
                    $statusBadge = match($p->status) {
                        'received'  => 'badge-success',
                        'pending'   => 'badge-warn',
                        'cancelled' => 'badge-danger',
                        default     => 'badge-muted',
                    };
                @endphp
                <tr>
                    <td class="td-mono" style="font-weight:600;">{{ $p->reference_no }}</td>
                    <td>{{ $p->supplier?->name ?? '—' }}</td>
                    <td class="td-mono">{{ $p->purchase_date?->format('Y-m-d') ?? '—' }}</td>
                    <td><span class="badge {{ $statusBadge }}">{{ ucfirst($p->status) }}</span></td>
                    <td class="mono" style="text-align:right;font-weight:700;">${{ number_format($p->grand_total, 2) }}</td>
                    <td class="mono" style="text-align:right;color:var(--success);">${{ number_format($p->paid_amount, 2) }}</td>
                    <td class="mono" style="text-align:right;color:{{ $p->due_amount > 0 ? 'var(--danger)' : 'var(--muted)' }};">
                        {{ $p->due_amount > 0 ? '$'.number_format($p->due_amount, 2) : '—' }}
                    </td>
                    <td class="td-mono">{{ $p->user?->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('purchases.show', $p) }}" class="btn btn-secondary btn-sm" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--muted);padding:40px;">
                        No purchase orders found for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($purchases->count())
            <tfoot style="border-top:2px solid var(--border);">
                <tr>
                    <td colspan="4" class="td-mono" style="padding:10px 14px;color:var(--muted);">
                        Page total
                    </td>
                    <td class="mono" style="padding:10px 14px;text-align:right;font-weight:700;color:var(--accent);">
                        ${{ number_format($purchases->sum('grand_total'), 2) }}
                    </td>
                    <td class="mono" style="padding:10px 14px;text-align:right;color:var(--success);">
                        ${{ number_format($purchases->sum('paid_amount'), 2) }}
                    </td>
                    <td class="mono" style="padding:10px 14px;text-align:right;color:var(--danger);">
                        ${{ number_format($purchases->sum('due_amount'), 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="pagination">{{ $purchases->links() }}</div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .card:first-child, .pagination, .btn { display: none !important; }
    .main { margin: 0; }
    .content { padding: 0; }
    body { background: #fff; color: #000; }
    .card { border: 1px solid #ccc; page-break-inside: avoid; }
    .stat-card::after { display: none; }
}
</style>
@endpush
