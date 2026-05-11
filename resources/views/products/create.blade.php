@extends('layouts.app')
@section('title', 'New Product')
@section('page-title', 'New Product')
@section('breadcrumb', 'Products / Create')

@section('content')
<div style="width:100%;">
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 320px;gap:16px;">

            {{-- ── Main Column ── --}}
            <div style="display:flex;flex-direction:column;gap:16px;">

                {{-- Basic Info --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Basic Info</span></div>

                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-control">
                            <option value="">Select brand…</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code') }}" required placeholder="e.g. PROD-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Barcode</label>
                            <input type="text" name="barcode" class="form-control"
                                   value="{{ old('barcode') }}" placeholder="Optional">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                                  placeholder="Optional product description…">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Selling Units --}}
                <div class="card">
                    <div class="card-header"><span class="card-title">Selling Units & Pricing</span></div>

                    {{-- PCS Unit (required) --}}
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
                                           value="{{ old('pcs_buying_price') }}" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Selling Price *</label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                    <input type="number" name="pcs_selling_price" id="pcsSellingPrice"
                                           step="0.01" min="0" class="form-control"
                                           style="padding-left:22px;"
                                           value="{{ old('pcs_selling_price') }}" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">PCS Barcode</label>
                                <input type="text" name="pcs_barcode" class="form-control"
                                       value="{{ old('pcs_barcode') }}" placeholder="Optional">
                            </div>
                        </div>
                        <div id="pcsMargin" style="margin-top:10px;font-size:12px;color:var(--muted);font-family:var(--mono);">
                            Margin: —
                        </div>
                    </div>

                    {{-- CASE Unit (optional) --}}
                    <div style="border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">
                        <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;background:var(--bg);margin:0;">
                            <input type="checkbox" name="has_case" id="hasCaseToggle" value="1"
                                   {{ old('has_case') ? 'checked' : '' }}
                                   style="width:16px;height:16px;cursor:pointer;">
                            <span style="font-weight:700;font-size:13px;">Enable CASE Unit</span>
                            <span style="font-size:11px;color:var(--muted);margin-left:4px;">— for bulk / carton selling</span>
                        </label>

                        <div id="caseUnitSection" style="{{ old('has_case') ? '' : 'display:none;' }}padding:14px;border-top:1px solid var(--border);">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Pieces per Case *</label>
                                    <input type="number" name="case_uom" id="caseUom"
                                           min="2" step="1" class="form-control"
                                           value="{{ old('case_uom', 24) }}" placeholder="e.g. 24">
                                    <div class="form-text">How many pieces = 1 case</div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Case Label</label>
                                    <input type="text" name="case_label" class="form-control"
                                           value="{{ old('case_label', 'CASE') }}"
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
                                               value="{{ old('case_buying_price') }}" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">Selling Price (per case)</label>
                                    <div style="position:relative;">
                                        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;">$</span>
                                        <input type="number" name="case_selling_price" id="caseSellingPrice"
                                               step="0.01" min="0" class="form-control"
                                               style="padding-left:22px;"
                                               value="{{ old('case_selling_price') }}" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label">CASE Barcode</label>
                                    <input type="text" name="case_barcode" class="form-control"
                                           value="{{ old('case_barcode') }}" placeholder="Optional">
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
                                   class="form-control" value="{{ old('stock_quantity', 0) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alert Quantity *</label>
                            <input type="number" name="alert_quantity" min="0"
                                   class="form-control" value="{{ old('alert_quantity', 5) }}" required>
                            <div class="form-text">Low-stock warning threshold</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax</label>
                        <select name="tax_id" class="form-control">
                            <option value="">No Tax</option>
                            @foreach($taxes as $tax)
                                <option value="{{ $tax->id }}"
                                    {{ old('tax_id') == $tax->id ? 'selected' : '' }}>
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
                                    {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->short_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-title">Image</span></div>
                    <div id="imagePreviewWrap" style="display:none;margin-bottom:10px;">
                        <img id="imagePreview" src="" alt="Preview"
                             style="width:100%;border-radius:4px;object-fit:cover;max-height:140px;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <input type="file" name="image" id="imageInput" class="form-control"
                               accept="image/jpg,image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG, WebP — max 2 MB</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Product
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── CASE toggle ───────────────────────────────────────────────────
document.getElementById('hasCaseToggle').addEventListener('change', function () {
    document.getElementById('caseUnitSection').style.display = this.checked ? '' : 'none';
    updateCaseAutoFill();
});

// ── Auto-fill case prices from pcs × uom ─────────────────────────
function updateCaseAutoFill() {
    if (!document.getElementById('hasCaseToggle').checked) return;
    const pcsBuy  = parseFloat(document.getElementById('pcsBuyingPrice').value)  || 0;
    const pcsSell = parseFloat(document.getElementById('pcsSellingPrice').value) || 0;
    const uom     = parseInt(document.getElementById('caseUom').value)            || 1;

    const buyEl  = document.getElementById('caseBuyingPrice');
    const sellEl = document.getElementById('caseSellingPrice');
    if (!buyEl.dataset.manual  && pcsBuy  > 0) buyEl.value  = (pcsBuy  * uom).toFixed(2);
    if (!sellEl.dataset.manual && pcsSell > 0) sellEl.value = (pcsSell * uom).toFixed(2);
    updateMargins();
}
['pcsBuyingPrice','pcsSellingPrice'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        updateMargins();
        updateCaseAutoFill();
    });
});
document.getElementById('caseUom').addEventListener('input', updateCaseAutoFill);
['caseBuyingPrice','caseSellingPrice'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        this.dataset.manual = '1';
        updateMargins();
    });
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

// ── Auto-generate code from name ──────────────────────────────────
const codeInput = document.querySelector('[name=code]');
document.querySelector('[name=name]').addEventListener('input', function () {
    if (!codeInput.dataset.manual) {
        codeInput.value = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 20);
    }
});
codeInput.addEventListener('input', function () { this.dataset.manual = '1'; });

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
