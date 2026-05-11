<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #111; margin: 0; padding: 20px; font-size: 13px; }
        .page { max-width: 860px; margin: 0 auto; }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 24px; line-height: 1.5; }
        .store-name-kh  { font-size: 26px; font-weight: 700; }
        .store-name-en  { font-size: 18px; font-weight: 700; letter-spacing: .04em; }
        .store-address  { font-size: 12px; color: #444; margin-top: 4px; }
        .invoice-title  { font-size: 28px; font-weight: 700; letter-spacing: .08em; margin-top: 14px; }
        .invoice-date   { font-size: 12px; color: #555; margin-top: 4px; }

        /* ── Info grid ── */
        .info-grid { display: flex; gap: 40px; margin-bottom: 20px; }
        .info-block { flex: 1; }
        .info-block .block-title { font-weight: 700; font-size: 12px; text-transform: uppercase;
            letter-spacing: .06em; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
        .info-row .val { font-weight: 600; text-align: right; max-width: 60%; }

        /* ── Items table ── */
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f0f0f0; font-size: 11px; text-transform: uppercase;
            letter-spacing: .04em; padding: 8px 7px; border: 1px solid #ccc; white-space: nowrap; }
        .table td { padding: 8px 7px; border: 1px solid #ddd; font-size: 12px; vertical-align: middle; }
        .table th.c, .table td.c { text-align: center; }
        .table th.r, .table td.r { text-align: right; }
        .table tbody tr:nth-child(even) { background: #fafafa; }

        /* ── Totals ── */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 24px; }
        .totals-box { width: 280px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; border-bottom: 1px solid #eee; }
        .total-row.grand { font-size: 15px; font-weight: 700; border-top: 2px solid #111; border-bottom: none; padding-top: 8px; margin-top: 4px; }
        .total-row.paid  { color: #555; }

        /* ── Signature ── */
        .signature-row { display: flex; gap: 40px; margin-top: 48px; }
        .signature-box { flex: 1; border-top: 1px dashed #999; padding-top: 8px;
            text-align: center; font-size: 12px; color: #555; }

        /* ── Footer ── */
        .footer { margin-top: 24px; font-size: 11px; color: #888; text-align: center; line-height: 1.8; }

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
            @foreach($sale->items as $index => $item)
                @php
                    $product    = $item->product;
                    $barcode    = $product->barcode ?? $product->code ?? '—';
                    $isCarton   = ($item->selling_unit ?? 'piece') === 'carton';
                    $unitLabel  = $isCarton ? 'CASE' : 'PCS';

                    // UOM: for CASE prefer packing parse when uom_case is 0/1 (not properly set)
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

                    // Unit Price is always per-piece so that: UOM × Qty × Unit Price = Amount
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
                    <td class="c">{{ $index + 1 }}</td>
                    <td>{{ $barcode }}</td>
                    <td>{{ trim(($product->brand->name ?? $product->brand_name ?? '') . ' ' . ($product->name ?? 'Deleted Product')) }}</td>
                    <td class="c">{{ $uomDisplay }}</td>
                    <td class="c">{{ $unitLabel }}</td>
                    <td class="c">{{ $item->quantity }}</td>
                    <td class="r">${{ number_format($displayPrice, 2) }}</td>
                    <td class="c">{{ $discDisplay }}</td>
                    <td class="r">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
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
