@extends('layouts.app')
@section('title', 'Edit Product')
@section('page-title', 'Edit Product')
@section('breadcrumb', 'Products / Edit')

@section('content')
@php
    $pcsUnit  = $product->productUnits->firstWhere('unit_type', 'piece');
    $caseUnit = $product->productUnits->firstWhere('unit_type', 'carton');
    $hasCase  = old('has_case', $caseUnit ? '1' : '0');
@endphp
<div style="width:100%;">
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 320px;gap:16px;">

            {{-- ── Main Column ── --}}
            <div style="display:flex;flex-direction:column;gap:16px;">

                {{-- Basic Info --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Basic Info</span></div>

                    <div class="form-group">
                        <label class="form-label">Product Name (English) *</label>
                        <input type="text" name="name_en" class="form-control"
                               value="{{ old('name_en', $product->name_en) }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Name (Khmer)</label>
                        <input type="text" name="name_kh" class="form-control"
                               value="{{ old('name_kh', $product->name_kh) }}" placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-control">
                            <option value="">Select brand…</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code', $product->code) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Barcode</label>
                            <input type="text" name="barcode" class="form-control"
                                   value="{{ old('barcode', $product->barcode) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                {{-- Selling Units --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Selling Units & Pricing</span></div>

                    {{-- PCS Unit --}}
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:14px;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                            <span style="background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.04em;">REQUIRED</span>
                            <span style="font-weight:700;font-size:13px;">PCS Unit (Piece)</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Buying Price *</label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                    <input type="number" name="pcs_buying_price" id="pcsBuyingPrice"
                                           step="0.01" min="0" class="form-control"
                                           style="padding-left:22px;"
                                           value="{{ old('pcs_buying_price', $pcsUnit->buying_price ?? $product->buying_price) }}"
                                           required placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Selling Price *</label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                    <input type="number" name="pcs_selling_price" id="pcsSellingPrice"
                                           step="0.01" min="0" class="form-control"
                                           style="padding-left:22px;"
                                           value="{{ old('pcs_selling_price', $pcsUnit->selling_price ?? $product->selling_price) }}"
                                           required placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">PCS Barcode</label>
                                <input type="text" name="pcs_barcode" class="form-control"
                                       value="{{ old('pcs_barcode', $pcsUnit->barcode ?? $product->barcode) }}"
                                       placeholder="Optional">
                            </div>
                        </div>
                        <div id="pcsMargin" style="margin-top:10px;font-size:12px;color:var(--muted);font-family:var(--mono);">
                            Margin: —
                        </div>
                    </div>

                    {{-- CASE Unit --}}
                    <div style="border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
                        <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;background:var(--bg);margin:0;">
                            <input type="checkbox" name="has_case" id="hasCaseToggle" value="1"
                                   {{ $hasCase ? 'checked' : '' }}
                                   style="width:16px;height:16px;cursor:pointer;">
                            <span style="font-weight:700;font-size:13px;">Enable CASE Unit</span>
                            <span style="font-size:11px;color:var(--muted);margin-left:4px;">— for bulk / carton selling</span>
                        </label>

                        <div id="caseUnitSection" style="{{ $hasCase ? '' : 'display:none;' }}padding:14px;border-top:1px solid var(--border);">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Pieces per Case *</label>
                                    <input type="number" name="case_uom" id="caseUom"
                                           min="2" step="1" class="form-control"
                                           value="{{ old('case_uom', $caseUnit->uom ?? 24) }}"
                                           placeholder="e.g. 24">
                                    <div class="form-text">How many pieces = 1 case</div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Case Label</label>
                                    <input type="text" name="case_label" class="form-control"
                                           value="{{ old('case_label', $caseUnit->label ?? 'CASE') }}"
                                           placeholder="CASE, CTN, BOX, PACK…">
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Buying Price (per case)</label>
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                        <input type="number" name="case_buying_price" id="caseBuyingPrice"
                                               step="0.01" min="0" class="form-control"
                                               style="padding-left:22px;"
                                               value="{{ old('case_buying_price', $caseUnit->buying_price ?? '') }}"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Selling Price (per case)</label>
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                        <input type="number" name="case_selling_price" id="caseSellingPrice"
                                               step="0.01" min="0" class="form-control"
                                               style="padding-left:22px;"
                                               value="{{ old('case_selling_price', $caseUnit->selling_price ?? '') }}"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">CASE Barcode</label>
                                    <input type="text" name="case_barcode" class="form-control"
                                           value="{{ old('case_barcode', $caseUnit->barcode ?? '') }}"
                                           placeholder="Optional">
                                </div>
                            </div>
                            <div id="caseMargin" style="margin-top:10px;font-size:12px;color:var(--muted);font-family:var(--mono);">
                                Margin: —
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stock --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Stock</span></div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Stock Qty (pieces) *</label>
                            <input type="number" name="stock_quantity" min="0"
                                   class="form-control"
                                   value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                            <div class="form-text">
                                @if($product->isLowStock())
                                    <span style="color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> Currently low stock</span>
                                @else
                                    Current: {{ $product->stock_quantity }} pcs
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alert Quantity *</label>
                            <input type="number" name="alert_quantity" min="0"
                                   class="form-control"
                                   value="{{ old('alert_quantity', $product->alert_quantity) }}" required>
                            <div class="form-text">Low-stock warning threshold</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax</label>
                        <select name="tax_id" class="form-control">
                            <option value="">No Tax</option>
                            @foreach($taxes as $tax)
                                <option value="{{ $tax->id }}"
                                    {{ old('tax_id', $product->tax_id) == $tax->id ? 'selected' : '' }}>
                                    {{ $tax->name }} ({{ $tax->rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Sidebar ── --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="card">
                    <div class="card-header"><span class="card-title">Organisation</span></div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Base Unit *</label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">Select…</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $product->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Image</span></div>
                    @if($product->image)
                        <div id="imagePreviewWrap" style="margin-bottom:10px;">
                            <img id="imagePreview" src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->name_en }}"
                                 style="width:100%;border-radius:4px;object-fit:cover;max-height:140px;">
                        </div>
                    @else
                        <div id="imagePreviewWrap" style="display:none;margin-bottom:10px;">
                            <img id="imagePreview" src="" alt="Preview"
                                 style="width:100%;border-radius:4px;object-fit:cover;max-height:140px;">
                        </div>
                    @endif
                    <div class="form-group" style="margin-bottom:0;">
                        <input type="file" name="image" id="imageInput" class="form-control"
                               accept="image/jpg,image/jpeg,image/png,image/webp">
                        <div class="form-text">Leave blank to keep current image</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Stats</span></div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach([
                            ['Created', $product->created_at->format('M d, Y')],
                            ['Updated', $product->updated_at->format('M d, Y')],
                            ['Units Sold', $product->saleItems()->sum('quantity')],
                            ['Transactions', $product->saleItems()->count()],
                        ] as [$label, $val])
                        <div style="display:flex;justify-content:space-between;font-size:13px;">
                            <span class="text-muted">{{ $label }}</span>
                            <span class="mono">{{ $val }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Product
            </button>
            <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── CASE toggle ───────────────────────────────────────────────────
document.getElementById('hasCaseToggle').addEventListener('change', function () {
    document.getElementById('caseUnitSection').style.display = this.checked ? '' : 'none';
    updateMargins();
});

// ── Margin display ────────────────────────────────────────────────
function updateMargins() {
    const buy  = parseFloat(document.getElementById('pcsBuyingPrice').value)  || 0;
    const sell = parseFloat(document.getElementById('pcsSellingPrice').value) || 0;
    const m    = sell - buy;
    const pct  = sell > 0 ? (m / sell * 100) : 0;
    const color = m >= 0 ? 'var(--success)' : 'var(--danger)';
    document.getElementById('pcsMargin').style.color = color;
    document.getElementById('pcsMargin').textContent =
        'Margin: $' + m.toFixed(2) + '  (' + pct.toFixed(1) + '%)';

    if (document.getElementById('hasCaseToggle').checked) {
        const cBuy  = parseFloat(document.getElementById('caseBuyingPrice').value)  || 0;
        const cSell = parseFloat(document.getElementById('caseSellingPrice').value) || 0;
        const cm    = cSell - cBuy;
        const cpct  = cSell > 0 ? (cm / cSell * 100) : 0;
        const cc    = cm >= 0 ? 'var(--success)' : 'var(--danger)';
        document.getElementById('caseMargin').style.color = cc;
        document.getElementById('caseMargin').textContent =
            'Margin: $' + cm.toFixed(2) + '  (' + cpct.toFixed(1) + '%)';
    }
}

['pcsBuyingPrice','pcsSellingPrice','caseBuyingPrice','caseSellingPrice'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateMargins);
});

updateMargins(); // populate on page load

// ── Image preview ─────────────────────────────────────────────────
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imagePreview').src = e.target.result;
        document.getElementById('imagePreviewWrap').style.display = '';
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
