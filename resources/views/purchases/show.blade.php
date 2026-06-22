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
                            <th>Buying Unit</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Stock Added</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        @php
                            $selUnit  = $item->selling_unit ?? 'piece';
                            $packSize = $item->pack_size    ?? 1;
                            $stockQty = $selUnit === 'carton' ? $item->quantity * $packSize : $item->quantity;
                        @endphp
                        <tr>
                            <td style="font-weight:500;">{{ $item->product->name_en ?? 'Deleted Product' }}</td>
                            <td class="td-mono">{{ $item->product->code ?? '—' }}</td>
                            <td>
                                @if($selUnit === 'carton')
                                    <span style="background:rgba(59,130,246,.15);color:#3b82f6;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;font-family:var(--mono);">CASE</span>
                                    <div style="font-size:10px;color:var(--muted);margin-top:2px;">1 case = {{ $packSize }} pcs</div>
                                @else
                                    <span style="background:rgba(34,197,94,.15);color:var(--success);font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;font-family:var(--mono);">PCS</span>
                                @endif
                            </td>
                            <td class="mono">{{ $item->quantity }}</td>
                            <td>
                                <div class="mono">${{ number_format($item->unit_cost, 2) }}</div>
                                @if($selUnit === 'carton' && $packSize > 1)
                                    <div style="font-size:10px;color:var(--muted);font-family:var(--mono);">
                                        ${{ number_format($item->unit_cost / $packSize, 4) }} / pcs
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="background:rgba(34,197,94,.08);color:var(--success);font-size:12px;font-weight:700;padding:3px 10px;border-radius:4px;font-family:var(--mono);border:1px solid rgba(34,197,94,.25);">
                                    +{{ $stockQty }} pcs
                                </span>
                            </td>
                            <td class="mono" style="font-weight:600;">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="border-top:2px solid var(--border);">
                        @php
                            $totalStockPcs = $purchase->items->sum(function($item) {
                                $selUnit  = $item->selling_unit ?? 'piece';
                                $packSize = $item->pack_size    ?? 1;
                                return $selUnit === 'carton' ? $item->quantity * $packSize : $item->quantity;
                            });
                        @endphp
                        <tr>
                            <td colspan="3" class="td-mono" style="padding:10px 14px;">TOTAL</td>
                            <td class="mono" style="padding:10px 14px;font-weight:700;">{{ $purchase->items->sum('quantity') }}</td>
                            <td></td>
                            <td style="padding:10px 14px;">
                                <span style="background:rgba(34,197,94,.08);color:var(--success);font-size:12px;font-weight:700;padding:3px 10px;border-radius:4px;font-family:var(--mono);border:1px solid rgba(34,197,94,.25);">
                                    +{{ $totalStockPcs }} pcs
                                </span>
                            </td>
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