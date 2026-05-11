<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #111; margin: 0; padding: 20px; }
        .page { max-width: 820px; margin: 0 auto; }
        .header, .footer { width: 100%; }
        .header { display: block; text-align: center; margin-bottom: 20px; }
        .store-details { display: inline-block; line-height: 1.4; text-align: center; }
        .store-name { font-size: 22px; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 8px; }
        .meta, .customer, .totals { width: 100%; margin-bottom: 16px; }
        .meta-row, .customer-row, .total-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px; }
        .section-title { font-size: 14px; font-weight: 700; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 10px 8px; border: 1px solid #ccc; font-size: 12px; }
        .table th { background: #f5f5f5; text-align: left; }
        .table td { vertical-align: top; }
        .table .qty, .table .price, .table .amount, .table .disc, .table .uom { text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: 700; }
        .no-print { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .btn-print { background: #111; color: #fff; }
        .btn-close { background: #f5f5f5; color: #111; margin-left: 8px; }
        .footer { margin-top: 30px; font-size: 12px; color: #555; line-height: 1.5; }
        .signature-row { display: flex; gap: 40px; margin-top: 40px; }
        .signature-box { width: 260px; border-top: 1px dashed #999; padding-top: 10px; text-align: center; color: #555; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="no-print">
        <a href="javascript:window.print()" class="btn btn-print">🖨 Print</a>
        <a href="javascript:window.close()" class="btn btn-close">Close</a>
    </div>

    <div class="header">
        <div class="store-details">
            <div class="store-name" style="font-size: xx-large;font-weight: bold;">អេស.ប៊ី.ធី ឌីស្រ្ទីប៊្យូធ័រ</div>
            <div class="store-name" style="font-size: large;font-weight: bold;">S.B.T DISTRIBUTOR</div>
            <div> អាសយដ្ឋាន​៖ ផ្លូវ៨១៤ ភូមិ៥ សង្កាត់៤ ក្រុងព្រះស៊ីហនុ ខេត្តព្រះស៊ីហនុ</div>
            <div> Address: St. 814, Phum 5, Sangkat 4, Preah Sihanouk Town, Preah Sihanouk Province</div>
            <div> ទូរស័ព្ទលេខ/ Tel: 016 249 777 / 068 55 97 168</div>
                        <div style="margin-top: 14px; font-size:28px; font-weight:700; letter-spacing:0.08em;">វិក្កយបត្រ</div>
            <div style="margin-top: 14px; font-size:28px; font-weight:700; letter-spacing:0.08em;">INVOICE</div>
            <div style="margin-top:10px;font-size:13px;color:#555;">{{ $sale->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="meta">
        <div class="section-title">Invoice Information</div>
        <div class="meta-row"><span>Invoice No:</span><span class="bold">{{ $sale->invoice_no }}</span></div>
        <div class="meta-row"><span>Payment Method:</span><span>{{ strtoupper($sale->payment_method ?? 'CASH') }}</span></div>
        <div class="meta-row"><span>Cashier:</span><span>{{ $sale->user->name ?? 'Admin' }}</span></div>
    </div>

    <div class="customer">
        <div class="section-title">Customer</div>
        <div class="customer-row"><span>Name:</span><span>{{ $sale->customer->name ?? 'Walk-in Customer' }}</span></div>
        <div class="customer-row"><span>Phone:</span><span>{{ $sale->customer->phone ?? 'N/A' }}</span></div>
        <div class="customer-row"><span>Address:</span><span>{{ $sale->customer->address ?? 'N/A' }}</span></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Barcode</th>
                <th>Description</th>
                <th class="uom">UOM</th>
                <th class="qty">Qty</th>
                <th class="price">Price</th>
                <th class="disc">Disc</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                @php
                    $product = $item->product;
                    $barcode = $product->barcode ?? $product->code ?? '—';
                    $unit = ($item->selling_unit ?? $item->unit_type ?? 'piece') === 'carton' ? 'CASE' : 'PCS';
                    $lineTotal = $item->quantity * $item->unit_price;
                    $discountPercent = $item->discount_amount > 0 && $lineTotal > 0
                        ? round($item->discount_amount / $lineTotal * 100, 1)
                        : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $barcode }}</td>
                    <td>{{ trim(($product->brand->name ?? '') . ' ' . ($product->name ?? 'Deleted Product')) }}</td>
                    <td class="text-center">{{ $unit }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-center">{{ $discountPercent > 0 ? $discountPercent.'%' : '—' }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row"><span>Subtotal</span><span>${{ number_format($sale->subtotal, 2) }}</span></div>
        @if($sale->discount_amount > 0)
            <div class="total-row"><span>Discount</span><span>-${{ number_format($sale->discount_amount, 2) }}</span></div>
        @endif
        @if($sale->tax_amount > 0)
            <div class="total-row"><span>Tax</span><span>${{ number_format($sale->tax_amount, 2) }}</span></div>
        @endif
        <div class="total-row bold"><span>Total</span><span>${{ number_format($sale->grand_total, 2) }}</span></div>
        @if($sale->paid_amount)
            <div class="total-row"><span>Paid</span><span>${{ number_format($sale->paid_amount, 2) }}</span></div>
            <div class="total-row"><span>Change</span><span>${{ number_format($sale->change_amount, 2) }}</span></div>
        @endif
    </div>

    @if($sale->notes)
        <div class="section-title">Notes</div>
        <div style="margin-bottom:16px;font-size:13px;color:#333;line-height:1.5;">{{ $sale->notes }}</div>
    @endif

    <div class="signature-row">
        <div class="signature-box">Customer Signature</div>
        <div class="signature-box">Seller Signature</div>
    </div>

    <div class="footer">
        <div>Thank you for your business.</div>
        <div>Please keep this invoice for your records.</div>
    </div>
</div>
</body>
</html>
