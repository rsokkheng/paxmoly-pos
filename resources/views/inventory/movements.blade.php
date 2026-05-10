@extends('layouts.app')
@section('title', 'Stock Movement Log')
@section('page-title', 'Stock Movement Log')
@section('breadcrumb', 'Inventory / Movements')

@section('topbar-actions')
    <a href="{{ route('inventory.adjustments.create') }}" class="btn btn-primary">
        <i class="fas fa-sliders-h"></i> New Adjustment
    </a>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('inventory.movements') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:2;min-width:180px;">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-control">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <option value="purchase"   {{ request('type') === 'purchase'   ? 'selected' : '' }}>Purchase (Stock In)</option>
                <option value="sale"       {{ request('type') === 'sale'       ? 'selected' : '' }}>Sale (Stock Out)</option>
                <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                <option value="return"     {{ request('type') === 'return'     ? 'selected' : '' }}>Return</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:140px;">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="form-group" style="margin:0;min-width:140px;">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('inventory.movements') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px;">
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Total Stock In</div>
        <div class="stat-value">{{ number_format($summary->total_in ?? 0) }}</div>
        <div class="stat-sub">units received / added</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Total Stock Out</div>
        <div class="stat-value">{{ number_format($summary->total_out ?? 0) }}</div>
        <div class="stat-sub">units sold / removed</div>
    </div>
    <div class="stat-card" style="--accent:#3b82f6;">
        <div class="stat-label">Total Movements</div>
        <div class="stat-value">{{ number_format($summary->total_movements ?? 0) }}</div>
        <div class="stat-sub">all-time log entries</div>
    </div>
</div>

<!-- Movement Log -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Movement History</span>
        <span class="badge badge-info">{{ $movements->total() }} records</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Change</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Reference</th>
                    <th>Notes</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td class="td-mono">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $m->product->name ?? '—' }}</div>
                        @if($m->product?->code)
                            <div class="td-mono">{{ $m->product->code }}</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $typeBadge = match($m->type) {
                                'purchase'   => ['badge-info',    'fa-truck',       'Purchase'],
                                'sale'       => ['badge-danger',  'fa-shopping-cart','Sale'],
                                'adjustment' => ['badge-warn',    'fa-sliders-h',   'Adjustment'],
                                'return'     => ['badge-success', 'fa-undo',        'Return'],
                                default      => ['badge-muted',   'fa-circle',      ucfirst($m->type)],
                            };
                        @endphp
                        <span class="badge {{ $typeBadge[0] }}">
                            <i class="fas {{ $typeBadge[1] }}" style="margin-right:4px;"></i>{{ $typeBadge[2] }}
                        </span>
                    </td>
                    <td class="mono" style="font-weight:700;color:{{ $m->quantity >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $m->quantity >= 0 ? '+' : '' }}{{ $m->quantity }}
                    </td>
                    <td class="td-mono">{{ $m->before_quantity }}</td>
                    <td class="td-mono" style="font-weight:500;">{{ $m->after_quantity }}</td>
                    <td class="td-mono">{{ $m->reference ?? '—' }}</td>
                    <td style="max-width:200px;white-space:normal;font-size:12px;color:var(--muted);">
                        {{ $m->notes ?? '—' }}
                    </td>
                    <td class="td-mono">{{ $m->user->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--muted);padding:40px;">
                        No stock movements found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $movements->links() }}</div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .card:first-child, .pagination, .btn { display: none !important; }
    .main { margin: 0; }
    .content { padding: 0; }
    body { background: #fff; color: #000; }
    .card { border: 1px solid #ccc; }
    .stat-card::after { display: none; }
}
</style>
@endpush
