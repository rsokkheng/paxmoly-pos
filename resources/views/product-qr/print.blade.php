<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Label Print</title>
    {{-- Pure JS QR code generator — no server-side package needed --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        /* ── Toolbar (hidden on print) ── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            background: #fff;
            padding: 14px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            flex-wrap: wrap;
        }
        .toolbar h2 { font-size: 16px; font-weight: 700; flex: 1; }
        .toolbar label { font-size: 13px; display: flex; align-items: center; gap: 6px; }
        .toolbar input[type=number] {
            width: 60px; border: 1px solid #d1d5db; border-radius: 4px;
            padding: 4px 8px; font-size: 13px; text-align: center;
        }
        .btn-print {
            background: #111; color: #fff; border: none; border-radius: 6px;
            padding: 8px 20px; font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .btn-back {
            background: #f0f0f0; color: #333; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 8px 16px; font-size: 13px; cursor: pointer; text-decoration: none;
        }

        /* ── Label grid ── */
        .label-sheet {
            display: grid;
            grid-template-columns: repeat(var(--cols, 4), 1fr);
            gap: 10px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ── Single label ── */
        .label {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 10px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .label-company {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .08em;
            color: #6b7280;
            text-transform: uppercase;
        }

        .label-qr {
            /* QRCode.js injects a canvas or img here */
        }
        .label-qr canvas, .label-qr img {
            width: 100px !important;
            height: 100px !important;
        }

        .label-title {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
            color: #111;
            max-width: 120px;
            word-break: break-word;
        }

        .label-subtitle {
            font-size: 13px;
            font-weight: 700;
            color: #111;
            font-family: monospace;
        }

        .label-barcode-text {
            font-size: 8px;
            color: #9ca3af;
            font-family: monospace;
            letter-spacing: .05em;
        }

        .label-unit-badge {
            display: inline-block;
            background: #f0b429;
            color: #000;
            font-size: 8px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
            letter-spacing: .05em;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .label-sheet {
                gap: 6px;
                max-width: 100%;
            }
            .label {
                border: 1px solid #ccc;
                border-radius: 4px;
                padding: 8px 6px;
            }
            @page { margin: 1cm; size: A4; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <h2><i class="fas fa-qrcode" style="color:#f0b429;margin-right:6px;"></i>QR Label Sheet</h2>
    <label>
        Columns:
        <input type="number" id="colsInput" value="4" min="2" max="8">
    </label>
    <button class="btn-print" onclick="window.print()">&#128438; Print</button>
    <a href="{{ url()->previous() }}" class="btn-back">&#8592; Back</a>
    <span style="font-size:12px;color:#6b7280;margin-left:auto;">
        {{ $labels->count() }} label(s)
    </span>
</div>

<div class="label-sheet" id="labelSheet" style="--cols:4">
    @forelse($labels as $qr)
    <div class="label">
        <div class="label-company">S.B.T DISTRIBUTOR</div>
        <div class="label-qr" id="qr_{{ $loop->index }}" data-content="{{ $qr->qr_content }}"></div>
        <div class="label-unit-badge">{{ strtoupper($qr->unit_type ?? 'ITEM') }}</div>
        <div class="label-title">{{ $qr->label_title }}</div>
        <div class="label-subtitle">{{ $qr->label_subtitle }}</div>
        @if($qr->label_barcode)
        <div class="label-barcode-text">{{ $qr->label_barcode }}</div>
        @endif
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:60px;color:#9ca3af;">
        No QR codes to print. Go back and generate QR codes first.
    </div>
    @endforelse
</div>

<script>
// Generate QR codes for every label div
document.querySelectorAll('.label-qr[data-content]').forEach(function(el) {
    new QRCode(el, {
        text:          el.dataset.content,
        width:         100,
        height:        100,
        colorDark:     '#111111',
        colorLight:    '#ffffff',
        correctLevel:  QRCode.CorrectLevel.M,
    });
});

// Column count control
document.getElementById('colsInput').addEventListener('input', function() {
    const cols = Math.max(2, Math.min(8, parseInt(this.value) || 4));
    document.getElementById('labelSheet').style.setProperty('--cols', cols);
});
</script>
</body>
</html>
