@extends('layouts.app')
@section('title', 'Sales')
@section('page-title', 'Sales')
@section('breadcrumb', 'Sales')

@section('topbar-actions')
    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <i class="fas fa-cash-register"></i> New Sale (POS)
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Sales</span>
        <form method="GET" action="{{ route('sales.index') }}" class="flex gap-2" id="filterForm">
            <div class="search-box" style="width:200px;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="form-control"
                       placeholder="Invoice no…"
                       value="{{ request('search') }}" id="searchInput">
            </div>
            <input type="date" name="date_from" class="form-control" style="width:145px;"
                   value="{{ request('date_from') }}" onchange="this.form.submit()">
            <input type="date" name="date_to" class="form-control" style="width:145px;"
                   value="{{ request('date_to') }}" onchange="this.form.submit()">
            <select name="status" class="form-control" style="width:130px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#Invoice</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td class="td-mono">{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                    {{-- FIX: items_count accessor on Sale model (sums quantity) --}}
                    <td>{{ $sale->items_count }}</td>
                    <td class="mono">${{ number_format($sale->subtotal, 2) }}</td>
                    <td class="mono text-danger">-${{ number_format($sale->discount_amount, 2) }}</td>
                    <td class="mono">${{ number_format($sale->tax_amount, 2) }}</td>
                    {{-- FIX: grand_total not total --}}
                    <td class="mono" style="font-weight:600;">${{ number_format($sale->grand_total, 2) }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($sale->payment_method ?? 'cash') }}</span></td>
                    <td>
                        <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($sale->status) }}
                        </span>
                    </td>
                    <td class="td-mono">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('sales.show', $sale) }}"
                               class="btn btn-secondary btn-icon btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('sales.invoice', $sale) }}"
                               class="btn btn-secondary btn-icon btn-sm" target="_blank" title="Print">
                                <i class="fas fa-print"></i>
                            </a>
                            @can('process_sale')
                            @if($sale->status === 'completed')
                            <a href="{{ route('sales.edit', $sale) }}"
                               class="btn btn-warning btn-icon btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;color:var(--muted);padding:40px;">
                        @if(request()->hasAny(['search','date_from','date_to','status']))
                            No sales match your filters.
                            <a href="{{ route('sales.index') }}" style="color:var(--accent);">Clear filters</a>
                        @else
                            No sales records yet.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 0;">
        <div style="font-size:12px;color:var(--muted);font-family:var(--mono);">
            {{ $sales->total() }} sale{{ $sales->total() !== 1 ? 's' : '' }}
        </div>
        <div class="pagination">{{ $sales->withQueryString()->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 400);
});
</script>
@endpush