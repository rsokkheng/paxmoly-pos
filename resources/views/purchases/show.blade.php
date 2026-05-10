@extends('layouts.app')
@section('title', 'Purchase '.$purchase->reference_no)
@section('page-title', 'Purchase — '.$purchase->reference_no)
@section('breadcrumb', 'Purchases / Detail')

@section('topbar-actions')
    @if($purchase->status === 'pending')
        <form action="{{ route('purchases.receive', $purchase) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Mark as received? This will update stock levels.')">
            @csrf @method('PATCH')
            <button class="btn btn-primary"><i class="fas fa-check-circle"></i> Mark Received</button>
        </form>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-secondary">
            <i class="fas fa-pencil"></i> Edit
        </a>
    @endif

    @if($purchase->status !== 'cancelled')
        <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Cancel this purchase?{{ $purchase->status === 'received' ? ' Stock will be reversed.' : '' }}')">
            @csrf @method('PATCH')
            <button class="btn btn-danger"><i class="fas fa-times-circle"></i> Cancel</button>
        </form>
    @endif
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;">

    {{-- LEFT: Items + Notes --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Items Ordered</span>
                <span class="badge badge-info">
                    {{ $purchase->items->count() }} line{{ $purchase->items->count() !== 1 ? 's' : '' }}
                </span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Discount</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td style="font-weight:500;">{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td class="td-mono">{{ $item->product->code ?? '—' }}</td>
                            <td>{{ $item->product->unit->abbreviation ?? '—' }}</td>
                            <td class="mono">{{ $item->quantity }}</td>
                            <td class="mono">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="mono">{{ $item->tax_amount > 0 ? '$'.number_format($item->tax_amount,2) : '—' }}</td>
                            <td class="mono">{{ $item->discount_amount > 0 ? '-$'.number_format($item->discount_amount,2) : '—' }}</td>
                            <td class="mono" style="font-weight:600;">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-top:2px solid var(--border);">
                        <tr>
                            <td colspan="3" class="td-mono" style="padding:10px 14px;">
                                TOTAL UNITS
                            </td>
                            <td class="mono" style="padding:10px 14px;font-weight:700;">
                                {{ $purchase->items->sum('quantity') }}
                            </td>
                            <td colspan="3"></td>
                            <td class="mono" style="padding:10px 14px;font-weight:700;font-size:15px;color:var(--accent);">
                                ${{ number_format($purchase->grand_total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($purchase->notes)
        <div class="card">
            <div class="card-header"><span class="card-title">Notes</span></div>
            <p style="font-size:13px;color:var(--muted);line-height:1.6;">{{ $purchase->notes }}</p>
        </div>
        @endif
    </div>

    {{-- RIGHT: Info + Financials --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="card">
            <div class="card-header"><span class="card-title">Order Info</span></div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @php
                    $statusBadge = match($purchase->status) {
                        'received'  => 'badge-success',
                        'pending'   => 'badge-warn',
                        'cancelled' => 'badge-danger',
                        default     => 'badge-muted',
                    };
                @endphp
                @foreach([
                    ['Reference',     $purchase->reference_no],
                    ['Supplier',      $purchase->supplier->name ?? '—'],
                    ['Purchase Date', $purchase->purchase_date?->format('M d, Y') ?? '—'],
                    ['Created By',    $purchase->user->name ?? '—'],
                    ['Created At',    $purchase->created_at->format('M d, Y H:i')],
                ] as [$label, $val])
                <div style="display:flex;justify-content:space-between;font-size:13px;align-items:center;">
                    <span class="text-muted">{{ $label }}</span>
                    <span class="{{ $label === 'Reference' ? 'td-mono' : '' }}">{{ $val }}</span>
                </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;font-size:13px;align-items:center;padding-top:8px;border-top:1px solid var(--border);margin-top:4px;">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $statusBadge }}">{{ ucfirst($purchase->status) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Financials</span></div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Subtotal</span>
                    <span class="mono">${{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Tax</span>
                    <span class="mono">${{ number_format($purchase->tax_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Discount</span>
                    <span class="mono text-success">-${{ number_format($purchase->discount_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;padding-top:10px;border-top:1px solid var(--border);margin-top:4px;">
                    <span>Grand Total</span>
                    <span class="mono text-accent">${{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Paid</span>
                    <span class="mono text-success">${{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Due</span>
                    {{-- due_amount is a model accessor: grand_total - paid_amount --}}
                    <span class="mono {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                        ${{ number_format($purchase->due_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection