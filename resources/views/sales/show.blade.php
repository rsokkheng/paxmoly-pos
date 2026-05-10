@extends('layouts.app')
@section('title', 'Sale #'.$sale->invoice_no)
@section('page-title', 'Sale #'.$sale->invoice_no)
@section('breadcrumb', 'Sales / Detail')

@section('topbar-actions')
    <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-secondary" target="_blank">
        <i class="fas fa-print"></i> Print Invoice
    </a>
    @can('cancel_sale')
    @if($sale->status === 'completed')
        <form action="{{ route('sales.cancel', $sale) }}" method="POST"
              onsubmit="return confirm('Cancel this sale and restore stock?')" style="display:inline;">
            @csrf @method('PATCH')
            <button class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancel Sale</button>
        </form>
    @endif
    @endcan
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;">

    <!-- Items -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Items Sold</span>
                {{-- items_count sums quantities; count() gives line count --}}
                <span class="badge badge-info">{{ $sale->items->count() }} line{{ $sale->items->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Tax</th>
                            <th>Discount</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td>
                                <div style="font-weight:500;">
                                    {{ $item->product->name ?? 'Deleted Product' }}
                                </div>
                                {{-- FIX: product->code not product->sku --}}
                                <div class="td-mono">{{ $item->product->code ?? '' }}</div>
                            </td>
                            <td class="mono">${{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="mono">
                                {{ $item->tax_amount > 0 ? '$'.number_format($item->tax_amount, 2) : '—' }}
                            </td>
                            {{-- FIX: discount_amount not discount --}}
                            <td class="mono">
                                {{ $item->discount_amount > 0 ? '-$'.number_format($item->discount_amount, 2) : '—' }}
                            </td>
                            <td class="mono" style="font-weight:600;">
                                ${{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($sale->notes)
        <div class="card">
            <div class="card-header"><span class="card-title">Notes</span></div>
            <p style="color:var(--muted);font-size:13px;">{{ $sale->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Summary -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Summary</span></div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach([
                    ['Customer',  $sale->customer->name ?? 'Walk-in'],
                    ['Invoice',   $sale->invoice_no],
                    ['Payment',   ucfirst($sale->payment_method ?? 'cash')],
                    ['Status',    ucfirst($sale->status)],
                    ['Date',      $sale->created_at->format('M d, Y H:i')],
                    ['Cashier',   $sale->user->name ?? '—'],
                ] as [$label, $val])
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">{{ $label }}</span>
                    <span>{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Subtotal</span>
                    <span class="mono">${{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">
                        Discount{{ $sale->discount ? ' ('.$sale->discount->code.')' : '' }}
                    </span>
                    <span class="mono text-danger">-${{ number_format($sale->discount_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Tax</span>
                    <span class="mono">${{ number_format($sale->tax_amount, 2) }}</span>
                </div>
                {{-- FIX: grand_total not total --}}
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:10px;border-top:1px solid var(--border);margin-top:4px;">
                    <span>Total</span>
                    <span class="mono text-accent">${{ number_format($sale->grand_total, 2) }}</span>
                </div>
                @if($sale->paid_amount)
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Paid</span>
                    <span class="mono">${{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Change</span>
                    {{-- FIX: use change_amount from DB, not re-calculating --}}
                    <span class="mono text-success">${{ number_format($sale->change_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection