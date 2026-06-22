@extends('layouts.app')
@section('title', 'Sale #'.$sale->invoice_no)
@section('page-title', 'Sale #'.$sale->invoice_no)
@section('breadcrumb', 'Sales / Detail')

@section('topbar-actions')
    <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-secondary" target="_blank">
        <i class="fas fa-print"></i> Print Old Invoice
    </a>
    <a href="{{ route('sales.invoice_new', $sale) }}" class="btn btn-primary" target="_blank">
        <i class="fas fa-file-invoice"></i> Print New Invoice
    </a>
    @can('process_sale')
    @if($sale->status === 'completed')
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Sale
        </a>
    @endif
    @endcan
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
        @php
            // Partition items: group set components by product_set_id, keep regular items in place
            $showRows   = [];
            $seenSetIds = [];
            foreach ($sale->items as $item) {
                if ($item->product_set_id) {
                    if (!in_array($item->product_set_id, $seenSetIds)) {
                        $seenSetIds[] = $item->product_set_id;
                        $components   = $sale->items->where('product_set_id', $item->product_set_id)->values();
                        $pSet         = $item->productSet;
                        $setsQty      = 1;
                        if ($pSet && $pSet->items->count() > 0) {
                            $defItem = $pSet->items->firstWhere('product_id', $item->product_id);
                            if ($defItem && $defItem->quantity > 0) {
                                $setsQty = max(1, (int) round($item->quantity / $defItem->quantity));
                            }
                        }
                        $setUnitPrice = $pSet ? (float) $pSet->selling_price : 0;
                        $showRows[] = [
                            'type'       => 'set',
                            'setName'    => $pSet ? $pSet->name : 'Product Set #'.$item->product_set_id,
                            'setsQty'    => $setsQty,
                            'unitPrice'  => $setUnitPrice,
                            'subtotal'   => $setUnitPrice * $setsQty,
                            'components' => $components,
                        ];
                    }
                } else {
                    $showRows[] = ['type' => 'product', 'item' => $item];
                }
            }
            $lineCount = count($showRows);
        @endphp
        <div class="card">
            <div class="card-header">
                <span class="card-title">Items Sold</span>
                <span class="badge badge-info">{{ $lineCount }} line{{ $lineCount !== 1 ? 's' : '' }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product / Set</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Tax</th>
                            <th>Discount</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($showRows as $row)
                        @if($row['type'] === 'set')
                        <tr style="background:rgba(240,180,41,.05);">
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;font-weight:600;">
                                    <span style="background:var(--accent);color:#000;font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;">SET</span>
                                    {{ $row['setName'] }}
                                </div>
                                <div style="font-size:11px;color:var(--muted);margin-top:4px;line-height:1.7;">
                                    @foreach($row['components'] as $comp)
                                        <span style="display:inline-block;margin-right:10px;">
                                            · {{ $comp->quantity }}× {{ $comp->product->name_en ?? '?' }}
                                            ({{ ($comp->selling_unit ?? 'piece') === 'carton' ? 'CASE' : 'PCS' }})
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="mono">${{ number_format($row['unitPrice'], 2) }}/set</td>
                            <td>{{ $row['setsQty'] }}</td>
                            <td class="mono">—</td>
                            <td class="mono">—</td>
                            <td class="mono" style="font-weight:600;">${{ number_format($row['subtotal'], 2) }}</td>
                        </tr>
                        @else
                        @php $item = $row['item']; @endphp
                        <tr>
                            <td>
                                <div style="font-weight:500;">{{ $item->product->name_en ?? 'Deleted Product' }}</div>
                                <div class="td-mono">{{ $item->product->code ?? '' }}</div>
                            </td>
                            <td class="mono">${{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="mono">{{ $item->tax_amount > 0 ? '$'.number_format($item->tax_amount,2) : '—' }}</td>
                            <td class="mono">{{ $item->discount_amount > 0 ? '-$'.number_format($item->discount_amount,2) : '—' }}</td>
                            <td class="mono" style="font-weight:600;">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endif
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