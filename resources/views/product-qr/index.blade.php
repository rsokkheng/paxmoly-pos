@extends('layouts.app')
@section('title', 'QR Code Labels')
@section('page-title', 'QR Code Labels')
@section('breadcrumb', 'Products / QR Codes')

@section('topbar-actions')
    <a href="{{ route('product-qr.print') }}?{{ request()->getQueryString() }}" target="_blank"
       class="btn btn-secondary"><i class="fas fa-print"></i> Print Selected</a>
@endsection

@section('content')

{{-- Filters --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:200px;">
            <label class="form-label">Search Product</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name or SKU…">
        </div>
        <div style="padding-top:20px;display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            <a href="{{ route('product-qr.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- QR content type selector --}}
<div class="card" style="margin-bottom:16px;">
    <div style="padding:14px 18px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span style="font-size:13px;font-weight:600;">QR Content Type:</span>
        @foreach(['barcode' => 'Barcode / SKU', 'sku' => 'SKU Only', 'json' => 'JSON (POS scan)'] as $type => $label)
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
            <input type="radio" name="content_type" value="{{ $type }}" id="ct_{{ $type }}"
                   {{ $type === 'barcode' ? 'checked' : '' }}>
            {{ $label }}
        </label>
        @endforeach
        <span style="font-size:11px;color:var(--muted);margin-left:auto;">
            <i class="fas fa-info-circle"></i>
            <em>Barcode</em>: uses the barcode field. <em>SKU</em>: uses product code. <em>JSON</em>: encodes ID + unit + price (for POS scanner).
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:16px;padding:12px 16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:var(--radius);color:var(--success);font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Product table --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">Products</span>
        <span style="font-size:12px;color:var(--muted);">{{ $products->total() }} products</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Units</th>
                    <th>QR Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $activeUnits = $product->productUnits->where('is_active', true);
                    $qrCodes     = $product->qrCodes->where('is_active', true);
                    $hasQr       = $qrCodes->isNotEmpty();
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $product->name }}</div>
                        <div style="font-size:11px;color:var(--muted);">{{ $product->category->name ?? '—' }}</div>
                    </td>
                    <td class="td-mono">{{ $product->code }}</td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @foreach($activeUnits as $u)
                            <span class="badge badge-secondary" style="font-size:10px;">
                                {{ $u->label }} · ${{ number_format($u->selling_price,2) }}
                                @if($u->barcode)
                                    <i class="fas fa-barcode" style="margin-left:3px;opacity:.6;" title="{{ $u->barcode }}"></i>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($hasQr)
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @foreach($qrCodes as $qr)
                                <span class="badge badge-success" style="font-size:10px;">
                                    <i class="fas fa-qrcode"></i>
                                    {{ $qr->unit_type ?? 'generic' }}
                                </span>
                            @endforeach
                            </div>
                        @else
                            <span style="font-size:12px;color:var(--muted);">Not generated</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            {{-- Generate / Regenerate --}}
                            <form method="POST" action="{{ route('product-qr.generate', $product) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="content_type" class="ct-hidden" value="barcode">
                                <button type="submit" class="btn btn-primary btn-sm" style="font-size:11px;">
                                    <i class="fas fa-qrcode"></i>
                                    {{ $hasQr ? 'Regenerate' : 'Generate' }}
                                </button>
                            </form>

                            {{-- Print this product --}}
                            @if($hasQr)
                            <a href="{{ route('product-qr.print') }}?product_id={{ $product->id }}"
                               target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px;">
                                <i class="fas fa-print"></i> Print
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $products->withQueryString()->links() }}</div>
</div>

@endsection

@push('scripts')
<script>
// Pass chosen content_type into each generate form
document.querySelectorAll('input[name="content_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.ct-hidden').forEach(function(h) {
            h.value = radio.value;
        });
    });
});
</script>
@endpush
