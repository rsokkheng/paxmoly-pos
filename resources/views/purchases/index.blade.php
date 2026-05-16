@extends('layouts.app')
@section('title', 'Purchases')
@section('page-title', 'Purchase Orders')
@section('breadcrumb', 'Purchases')

@section('topbar-actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Purchase
    </a>
@endsection

@section('content')

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-label">Total Value</div>
        <div class="stat-value">${{ number_format($totals->total ?? 0, 2) }}</div>
        <div class="stat-sub">filtered purchases</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value">${{ number_format($totals->paid ?? 0, 2) }}</div>
        <div class="stat-sub">amount settled</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Total Due</div>
        <div class="stat-value">${{ number_format($totals->due ?? 0, 2) }}</div>
        <div class="stat-sub">outstanding balance</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">All Purchases</span>
        <form method="GET" action="{{ route('purchases.index') }}" class="flex gap-2" id="filterForm">
            <div class="search-box" style="width:190px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" id="searchInput" class="form-control"
                       placeholder="Ref or supplier…" value="{{ request('search') }}">
            </div>
            <select name="supplier_id" class="form-control" style="width:150px;" onchange="this.form.submit()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="width:130px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="received"  {{ request('status') === 'received'  ? 'selected' : '' }}>Received</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" class="form-control" style="width:145px;"
                   value="{{ request('date_from') }}" onchange="this.form.submit()">
            <input type="date" name="date_to" class="form-control" style="width:145px;"
                   value="{{ request('date_to') }}" onchange="this.form.submit()">
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th style="width:110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td class="td-mono">{{ $purchase->reference_no }}</td>
                    <td style="font-weight:500;">{{ $purchase->supplier->name ?? '—' }}</td>
                    <td class="td-mono">{{ $purchase->purchase_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $purchase->items_count }}</td>
                    <td class="mono">${{ number_format($purchase->grand_total, 2) }}</td>
                    <td class="mono text-success">${{ number_format($purchase->paid_amount, 2) }}</td>
                    <td class="mono {{ $purchase->due_amount > 0 ? 'text-danger' : '' }}">
                        ${{ number_format($purchase->due_amount, 2) }}
                    </td>
                    <td>
                        @php
                            $badge = match($purchase->status) {
                                'received'  => 'badge-success',
                                'pending'   => 'badge-warn',
                                'cancelled' => 'badge-danger',
                                default     => 'badge-muted',
                            };
                        @endphp
                        <span class="badge {{ $badge }}">{{ ucfirst($purchase->status) }}</span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('purchases.show', $purchase) }}"
                               class="btn btn-secondary btn-icon btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($purchase->status === 'pending')
                            <a href="{{ route('purchases.edit', $purchase) }}"
                               class="btn btn-secondary btn-icon btn-sm" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--muted);padding:48px;">
                        <i class="fas fa-truck" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3;"></i>
                        @if(request()->hasAny(['search','supplier_id','status','date_from','date_to']))
                            No purchases match your filters.
                            <a href="{{ route('purchases.index') }}" style="color:var(--accent);">Clear filters</a>
                        @else
                            No purchase orders yet.
                            <a href="{{ route('purchases.create') }}" style="color:var(--accent);">Create one</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 0;">
        <div style="font-size:12px;color:var(--muted);font-family:var(--mono);">
            {{ $purchases->total() }} purchase{{ $purchases->total() !== 1 ? 's' : '' }}
        </div>
        <div class="pagination">{{ $purchases->withQueryString()->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let t;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(() => document.getElementById('filterForm').submit(), 400);
});
</script>
@endpush