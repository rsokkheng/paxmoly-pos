<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #111; margin: 0; padding: 16px; font-size: 11px; }
        .page { max-width: 860px; margin: 0 auto; }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 16px; line-height: 1.4; }
        .store-name-kh  { font-size: 20px; font-weight: 700; }
        .store-name-en  { font-size: 14px; font-weight: 700; letter-spacing: .04em; }
        .store-address  { font-size: 10px; color: #444; margin-top: 3px; }
        .invoice-title  { font-size: 20px; font-weight: 700; letter-spacing: .08em; margin-top: 10px; }
        .invoice-date   { font-size: 10px; color: #555; margin-top: 3px; }

        /* ── Info grid ── */
        .info-grid { display: flex; gap: 32px; margin-bottom: 14px; }
        .info-block { flex: 1; }
        .info-block .block-title { font-weight: 700; font-size: 10px; text-transform: uppercase;
            letter-spacing: .06em; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 6px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 10px; }
        .info-row .val { font-weight: 600; text-align: right; max-width: 60%; }

        /* ── Items table ── */
        .table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .table th { background: #f0f0f0; font-size: 9px; text-transform: uppercase;
            letter-spacing: .04em; padding: 5px 6px; border: 1px solid #ccc; white-space: nowrap; }
        .table td { padding: 5px 6px; border: 1px solid #ddd; font-size: 10px; vertical-align: middle; }
        .table th.c, .table td.c { text-align: center; }
        .table th.r, .table td.r { text-align: right; }
        .table tbody tr:nth-child(even) { background: #fafafa; }

        /* ── Totals ── */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 16px; }
        .totals-box { width: 240px; }
        .total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; border-bottom: 1px solid #eee; }
        .total-row.grand { font-size: 13px; font-weight: 700; border-top: 2px solid #111; border-bottom: none; padding-top: 6px; margin-top: 3px; }
        .total-row.paid  { color: #555; }

        /* ── Signature ── */
        .signature-row { display: flex; gap: 32px; margin-top: 36px; }
        .signature-box { flex: 1; border-top: 1px dashed #999; padding-top: 6px;
            text-align: center; font-size: 10px; color: #555; }

        /* ── Footer ── */
        .footer { margin-top: 16px; font-size: 9px; color: #888; text-align: center; line-height: 1.7; }

        /* ── Print controls ── */
        .no-print { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 9px 18px; border-radius: 4px; text-decoration: none;
            font-size: 13px; cursor: pointer; border: none; }
        .btn-print { background: #111; color: #fff; }
        .btn-close { background: #f0f0f0; color: #111; margin-left: 8px; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .table tbody tr:nth-child(even) { background: #fff; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Print / Close buttons --}}
    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">🖨 Print</button>
        <a class="btn btn-close" href="javascript:window.close()">Close</a>
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="store-name-kh">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</div>
        <div class="store-name-en">S.B.T DISTRIBUTOR</div>
        <div class="store-address">អាសយដ្ឋាន​៖ ផ្លូវ៨១៤ ភូមិ៥ សង្កាត់៤ ក្រុងព្រះស៊ីហនុ ខេត្តព្រះស៊ីហនុ</div>
        <div class="store-address">St. 814, Phum 5, Sangkat 4, Preah Sihanouk Town, Preah Sihanouk Province</div>
        <div class="store-address">ទូរស័ព្ទ / Tel: 016 249 777 &nbsp;|&nbsp; 068 55 97 168</div>
        <div class="invoice-title">វិក្កយបត្រ / INVOICE</div>
        <div class="invoice-date">{{ $sale->created_at->format('d M Y  H:i') }}</div>
    </div>

    {{-- Invoice info + Customer --}}
    <div class="info-grid">
        <div class="info-block">
            <div class="block-title">Invoice Information</div>
            <div class="info-row"><span>Invoice No</span><span class="val">{{ $sale->invoice_no }}</span></div>
            <div class="info-row"><span>Payment Method</span><span class="val">{{ strtoupper($sale->payment_method ?? 'CASH') }}</span></div>
            <div class="info-row"><span>Cashier</span><span class="val">{{ $sale->user->name ?? 'Admin' }}</span></div>
        </div>
        <div class="info-block">
            <div class="block-title">Customer</div>
            <div class="info-row"><span>Name</span><span class="val">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span></div>
            <div class="info-row"><span>Phone</span><span class="val">{{ $sale->customer->phone ?? '—' }}</span></div>
            <div class="info-row"><span>Address</span><span class="val">{{ $sale->customer->address ?? '—' }}</span></div>
        </div>
    </div>

    {{-- Items table --}}
    @php
        // Partition: group set-tagged items by product_set_id, keep regular items in order
        $invoiceRows = [];
        $seenSetIds  = [];

        foreach ($sale->items as $item) {
            if ($item->product_set_id) {
                if (!in_array($item->product_set_id, $seenSetIds)) {
                    $seenSetIds[] = $item->product_set_id;
                    // Collect all components for this set
                    $components = $sale->items->where('product_set_id', $item->product_set_id)->values();
                    $pSet       = $item->productSet;

                    // Derive how many full sets were sold
                    $setsQty = 1;
                    if ($pSet && $pSet->items->count() > 0) {
                        $defItem = $pSet->items->firstWhere('product_id', $item->product_id);
                        if ($defItem && $defItem->quantity > 0) {
                            $setsQty = max(1, (int) round($item->quantity / $defItem->quantity));
                        }
                    }

                    $setUnitPrice = $pSet ? (float) $pSet->selling_price : 0;
                    $setSubtotal  = $setUnitPrice * $setsQty;

                    $invoiceRows[] = [
                        'type'       => 'set',
                        'setName'    => $pSet ? $pSet->name : 'Product Set #' . $item->product_set_id,
                        'setCode'    => $pSet ? $pSet->code : '',
                        'setsQty'    => $setsQty,
                        'unitPrice'  => $setUnitPrice,
                        'subtotal'   => $setSubtotal,
                        'components' => $components,
                    ];
                }
            } else {
                $invoiceRows[] = ['type' => 'product', 'item' => $item];
            }
        }
    @endphp

    <table class="table">
        <thead>
            <tr>
                <th class="c" style="width:36px;">#</th>
                <th>Barcode</th>
                <th>Description</th>
                <th class="c" style="width:54px;">UOM</th>
                <th class="c" style="width:54px;">Unit</th>
                <th class="c" style="width:50px;">Qty</th>
                <th class="r" style="width:88px;">Unit Price</th>
                <th class="c" style="width:54px;">Disc</th>
                <th class="r" style="width:90px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceRows as $rowIdx => $row)
                @if($row['type'] === 'set')
                    @php
                        $firstComp = $row['components']->first();
                        if ($firstComp && $firstComp->discount_type === 'pct' && $firstComp->discount_value > 0) {
                            $discDisplay = $firstComp->discount_value . '%';
                        } elseif ($firstComp && $firstComp->discount_type === 'amt' && $firstComp->discount_value > 0) {
                            $discDisplay = '$' . number_format($firstComp->discount_value, 2);
                        } elseif ($firstComp && $firstComp->discount_amount > 0) {
                            $discDisplay = '$' . number_format($firstComp->discount_amount, 2);
                        } else {
                            $discDisplay = '—';
                        }
                    @endphp
                    <tr style="background:#fffbea;">
                        <td class="c">{{ $rowIdx + 1 }}</td>
                        <td style="font-family:monospace;font-size:11px;">{{ $row['setCode'] ?: '—' }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:5px;margin-bottom:4px;">
                                <span style="background:#f0b429;color:#000;font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;letter-spacing:.04em;">SET</span>
                                <strong>{{ $row['setName'] }}</strong>
                            </div>
                            <div style="font-size:10px;color:#666;line-height:1.7;padding-left:2px;">
                                @foreach($row['components'] as $comp)
                                    @php
                                        $compUnit = ($comp->selling_unit ?? 'piece') === 'carton' ? 'CASE' : 'PCS';
                                    @endphp
                                    <span style="display:inline-block;margin-right:10px;">
                                        · {{ $comp->quantity }}× {{ $comp->product->name ?? '?' }} ({{ $compUnit }})
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="c">1</td>
                        <td class="c">SET</td>
                        <td class="c">{{ $row['setsQty'] }}</td>
                        <td class="r">${{ number_format($row['unitPrice'], 2) }}</td>
                        <td class="c">{{ $discDisplay }}</td>
                        <td class="r">${{ number_format($row['subtotal'], 2) }}</td>
                    </tr>
                @else
                    @php
                        $item       = $row['item'];
                        $product    = $item->product;
                        $barcode    = $product->barcode ?? $product->code ?? '—';
                        $isCarton   = ($item->selling_unit ?? 'piece') === 'carton';
                        $unitLabel  = $isCarton ? 'CASE' : 'PCS';

                        if ($isCarton) {
                            $uomRaw = (float)($product->uom_case ?? 0);
                            if ($uomRaw <= 1) {
                                $fromPacking = \App\Models\Product::parsePackingSize($product->packing ?? null);
                                if ($fromPacking > 1) $uomRaw = $fromPacking;
                            }
                            if ($uomRaw <= 0) $uomRaw = 1;
                        } else {
                            $uomRaw = max(1, (float)($product->uom_pcs ?? 1));
                        }
                        $uomDisplay = (floor($uomRaw) == $uomRaw) ? (int)$uomRaw : $uomRaw;

                        $displayPrice = ($isCarton && $uomRaw > 1)
                            ? round((float)$item->unit_price / $uomRaw, 4)
                            : (float)$item->unit_price;

                        if ($item->discount_type === 'pct' && $item->discount_value > 0) {
                            $discDisplay = $item->discount_value . '%';
                        } elseif ($item->discount_type === 'amt' && $item->discount_value > 0) {
                            $discDisplay = '$' . number_format($item->discount_value, 2);
                        } elseif ($item->discount_amount > 0) {
                            $discDisplay = '$' . number_format($item->discount_amount, 2);
                        } else {
                            $discDisplay = '—';
                        }
                    @endphp
                    <tr>
                        <td class="c">{{ $rowIdx + 1 }}</td>
                        <td>{{ $barcode }}</td>
                        <td>{{ trim(($product->brand->name ?? $product->brand_name ?? '') . ' ' . ($product->name ?? 'Deleted Product')) }}</td>
                        <td class="c">{{ $uomDisplay }}</td>
                        <td class="c">{{ $unitLabel }}</td>
                        <td class="c">{{ $item->quantity }}</td>
                        <td class="r">${{ number_format($displayPrice, 2) }}</td>
                        <td class="c">{{ $discDisplay }}</td>
                        <td class="r">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-box">
            <div class="total-row"><span>Subtotal</span><span>${{ number_format($sale->subtotal, 2) }}</span></div>
            @if($sale->discount_amount > 0)
                <div class="total-row"><span>Discount</span><span>-${{ number_format($sale->discount_amount, 2) }}</span></div>
            @endif
            @if($sale->tax_amount > 0)
                <div class="total-row"><span>Tax</span><span>${{ number_format($sale->tax_amount, 2) }}</span></div>
            @endif
            <div class="total-row grand"><span>Total</span><span>${{ number_format($sale->grand_total, 2) }}</span></div>
            @if($sale->paid_amount)
                <div class="total-row paid"><span>Paid</span><span>${{ number_format($sale->paid_amount, 2) }}</span></div>
                <div class="total-row paid"><span>Change</span><span>${{ number_format($sale->change_amount, 2) }}</span></div>
            @endif
        </div>
    </div>

    @if($sale->notes)
        <div style="margin-bottom:20px;">
            <div style="font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Notes</div>
            <div style="font-size:13px;color:#333;line-height:1.6;">{{ $sale->notes }}</div>
        </div>
    @endif

    {{-- Signatures --}}
    <div class="signature-row">
        <div class="signature-box">Customer Signature</div>
        <div class="signature-box">Seller Signature</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div>Thank you for your business.</div>
        <div>Please keep this invoice for your records.</div>
    </div>

</div>
</body>
</html>
