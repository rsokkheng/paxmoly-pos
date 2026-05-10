@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', $customer->name)
@section('breadcrumb', 'Customers / Detail')

@section('topbar-actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary"><i class="fas fa-pencil"></i> Edit</a>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title">Customer Info</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                @foreach([
                    ['Name', $customer->name],
                    ['Email', $customer->email ?? '—'],
                    ['Phone', $customer->phone ?? '—'],
                    ['Date of Birth', $customer->dob?->format('M d, Y') ?? '—'],
                    ['City', $customer->city ?? '—'],
                    ['Country', $customer->country ?? '—'],
                ] as [$label, $val])
                <div>
                    <div class="form-label">{{ $label }}</div>
                    <div style="font-size:14px;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Purchase History -->
        <div class="card">
            <div class="card-header"><span class="card-title">Purchase History</span></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>#Invoice</th><th>Items</th><th>Total</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($customer->sales ?? [] as $sale)
                        <tr>
                            <td class="td-mono">{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td class="mono">${{ number_format($sale->total, 2) }}</td>
                            <td class="td-mono">{{ $sale->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px;">No purchases yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="--accent:var(--accent);">
            <div class="form-label">Total Spent</div>
            <div class="stat-value">${{ number_format($customer->total_spent ?? 0, 2) }}</div>
            <div class="form-text">across {{ $customer->orders_count ?? 0 }} orders</div>
        </div>
        <div class="card">
            <div class="form-label">Member Since</div>
            <div style="font-size:18px;font-weight:600;font-family:var(--mono);margin:6px 0 4px;">{{ $customer->created_at->format('M Y') }}</div>
            <div class="form-text">{{ $customer->created_at->diffForHumans() }}</div>
        </div>
    </div>
</div>
@endsection
