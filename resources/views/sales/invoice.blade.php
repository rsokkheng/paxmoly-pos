<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; background: #fff; color: #111; font-size: 12px; padding: 20px; max-width: 320px; margin: 0 auto; }
        .receipt-header { text-align: center; border-bottom: 1px dashed #999; padding-bottom: 12px; margin-bottom: 12px; }
        .store-name { font-family: 'Hanuman', serif; font-size: 17px; font-weight: bold; margin-bottom: 2px; }
        .store-info { font-size: 10px; color: #555; line-height: 1.5; }
        .invoice-meta { margin-bottom: 12px; border-bottom: 1px dashed #999; padding-bottom: 12px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 11px; }
        .meta-label { color: #555; }
        .items-header { display: flex; font-size: 10px; color: #555; border-bottom: 1px solid #999; padding-bottom: 4px; margin-bottom: 4px; }
        .items-header span:first-child { flex: 1; }
        .items-header span:nth-child(2) { width: 36px; text-align: center; }
        .items-header span:nth-child(3) { width: 46px; text-align: right; }
        .items-header span:nth-child(4) { width: 44px; text-align: right; }
        .items-header span:last-child { width: 54px; text-align: right; }
        .item { display: flex; align-items: baseline; margin-bottom: 6px; }
        .item-name { flex: 1; line-height: 1.3; }
        .item-qty { width: 36px; text-align: center; }
        .item-price { width: 46px; text-align: right; }
        .item-disc { width: 44px; text-align: right; color: #c00; }
        .item-total { width: 54px; text-align: right; font-weight: bold; }
        .totals { border-top: 1px dashed #999; margin-top: 10px; padding-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 11px; }
        .grand-total { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; border-top: 2px solid #111; border-bottom: 2px solid #111; padding: 6px 0; margin: 6px 0; }
        .receipt-footer { text-align: center; margin-top: 16px; padding-top: 12px; border-top: 1px dashed #999; font-size: 10px; color: #555; line-height: 1.6; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 0.5cm; size: 80mm auto; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:16px;display:flex;gap:8px;">
    <button onclick="window.print()" style="padding:8px 16px;background:#111;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
        🖨 Print
    </button>
    <button onclick="window.close()" style="padding:8px 16px;background:#f0f0f0;color:#111;border:none;border-radius:4px;cursor:pointer;font-size:13px;">
        Close
    </button>
</div>

<div class="receipt-header">
    <div class="store-name">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</div>
    <div class="store-info" style="font-size:11px;font-weight:bold;margin-bottom:3px;">S.B.T DISTRIBUTOR</div>
    <div class="store-info">
        អាសយដ្ឋាន​៖ ផ្លូវ៨១៤ ភូមិ៥ សង្កាត់៤ ក្រុងព្រះស៊ីហនុ ខេត្តព្រះស៊ីហនុ<br>
        St. 814, Phum 5, Sangkat 4, Preah Sihanouk Town<br>
        Tel: 016 249 777 &nbsp;|&nbsp; 068 55 97 168
    </div>
</div>

<div class="invoice-meta">
    <div class="meta-row">
        <span class="meta-label">Invoice No</span>
        <span><strong>{{ $sale->invoice_no }}</strong></span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Date</span>
        <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Customer</span>
        <span>{{ $sale->customer->name ?? 'Walk-in' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Cashier</span>
        <span>{{ $sale->user->name ?? 'Admin' }}</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Payment</span>
        <span>{{ strtoupper($sale->payment_method ?? 'CASH') }}</span>
    </div>
</div>

@php
    // Partition items: group set components, keep regular items in order
    $thermalRows = [];
    $seenSetIds  = [];
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
                $thermalRows[] = [
                    'type'       => 'set',
                    'setName'    => $pSet ? $pSet->name : 'Set #'.$item->product_set_id,
                    'setsQty'    => $setsQty,
                    'unitPrice'  => $setUnitPrice,
                    'subtotal'   => $setUnitPrice * $setsQty,
                    'components' => $components,
                ];
            }
        } else {
            $thermalRows[] = ['type' => 'product', 'item' => $item];
        }
    }
    $hasItemDisc = $sale->items->whereNull('product_set_id')->sum('discount_amount') > 0;
@endphp

<div class="items-header">
    <span>ITEM</span>
    <span>QTY</span>
    <span>PRICE</span>
    @if($hasItemDisc)<span>DISC</span>@endif
    <span>TOTAL</span>
</div>

@foreach($thermalRows as $row)
@if($row['type'] === 'set')
<div class="item" style="flex-direction:column;align-items:flex-start;">
    <div style="display:flex;width:100%;">
        <div class="item-name" style="font-weight:bold;">
            [SET] {{ $row['setName'] }}
        </div>
        <div class="item-qty">{{ $row['setsQty'] }}</div>
        <div class="item-price">${{ number_format($row['unitPrice'], 2) }}</div>
        @if($hasItemDisc)<div class="item-disc">—</div>@endif
        <div class="item-total">${{ number_format($row['subtotal'], 2) }}</div>
    </div>
    <div style="font-size:10px;color:#666;padding-left:4px;line-height:1.6;">
        @foreach($row['components'] as $comp)
            {{ $comp->quantity }}× {{ $comp->product->name_en ?? '?' }}
            ({{ ($comp->selling_unit ?? 'piece') === 'carton' ? 'CASE' : 'PCS' }})<br>
        @endforeach
    </div>
</div>
@else
@php $item = $row['item']; @endphp
<div class="item">
    <div class="item-name">{{ $item->product->name_en ?? 'Item' }}</div>
    <div class="item-qty">{{ $item->quantity }}</div>
    <div class="item-price">${{ number_format($item->unit_price, 2) }}</div>
    @if($hasItemDisc)
        <div class="item-disc">
            {{ $item->discount_amount > 0 ? '-$'.number_format($item->discount_amount, 2) : '—' }}
        </div>
    @endif
    <div class="item-total">${{ number_format($item->subtotal, 2) }}</div>
</div>
@endif
@endforeach

<div class="totals">
    <div class="total-row">
        <span>Subtotal</span>
        <span>${{ number_format($sale->subtotal, 2) }}</span>
    </div>

    @if($sale->discount_amount > 0)
    <div class="total-row">
        {{-- FIX: no discount_code column — use relation $sale->discount->code --}}
        <span>Discount{{ $sale->discount ? ' ('.$sale->discount->code.')' : '' }}</span>
        <span>-${{ number_format($sale->discount_amount, 2) }}</span>
    </div>
    @endif

    @if($sale->tax_amount > 0)
    <div class="total-row">
        <span>Tax</span>
        <span>${{ number_format($sale->tax_amount, 2) }}</span>
    </div>
    @endif

    {{-- FIX: grand_total not total --}}
    <div class="grand-total">
        <span>TOTAL</span>
        <span>${{ number_format($sale->grand_total, 2) }}</span>
    </div>

    @if($sale->paid_amount)
    <div class="total-row">
        <span>Paid</span>
        <span>${{ number_format($sale->paid_amount, 2) }}</span>
    </div>
    <div class="total-row">
        <span>Change</span>
        {{-- FIX: use stored change_amount not re-calculation --}}
        <span>${{ number_format($sale->change_amount, 2) }}</span>
    </div>
    @endif
</div>

<div class="receipt-footer">
    ********************************<br>
    សូមអរគុណ! Thank you!<br>
    Please keep this receipt for<br>
    exchange within 7 days.<br>
    ********************************<br>
    S.B.T DISTRIBUTOR
</div>

<script>
if (new URLSearchParams(location.search).get('print') === '1') window.print();
</script>
</body>
</html>