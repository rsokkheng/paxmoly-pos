@extends('layouts.app')
@section('title', 'New Product')
@section('page-title', 'New Product')
@section('breadcrumb', 'Products / Create')

@section('content')
<div style="max-width:780px;">
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 280px;gap:16px;">

            <!-- Main Column -->
            <div style="display:flex;flex-direction:column;gap:16px;">
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
                        {{-- Controller validates: 'code' (not 'sku') --}}
                        <div class="form-group">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code') }}" required
                                   placeholder="e.g. PROD-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Barcode</label>
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

                <div class="card">
                    <div class="card-header"><span class="card-title">Pricing & Stock</span></div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Buying Price *</label>
                            <input type="number" name="buying_price" step="0.01" min="0"
                                   class="form-control" value="{{ old('buying_price') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Selling Price *</label>
                            <input type="number" name="selling_price" step="0.01" min="0"
                                   class="form-control" value="{{ old('selling_price') }}" required>
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label class="form-label">Stock Qty *</label>
                            <input type="number" name="stock_quantity" min="0"
                                   class="form-control" value="{{ old('stock_quantity', 0) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Packing</label>
                            <input type="text" name="packing" class="form-control"
                                   value="{{ old('packing') }}" placeholder="e.g. 24 pcs/ctn">
                        </div>
                    </div>

                    <div class="form-row cols-2">
                        {{-- Controller validates: 'alert_quantity' (not 'min_stock') --}}
                        <div class="form-group">
                            <label class="form-label">Alert Quantity *</label>
                            <input type="number" name="alert_quantity" min="0"
                                   class="form-control" value="{{ old('alert_quantity', 5) }}" required>
                            <div class="form-text">Low stock warning threshold</div>
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

                    {{-- Live margin preview --}}
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:12px;display:flex;gap:24px;">
                        <div>
                            <div class="form-label">Gross Margin</div>
                            <div id="marginVal" style="font-family:var(--mono);font-size:16px;color:var(--accent);">—</div>
                        </div>
                        <div>
                            <div class="form-label">Margin %</div>
                            <div id="marginPct" style="font-family:var(--mono);font-size:16px;color:var(--accent);">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="card">
                    <div class="card-header"><span class="card-title">Organisation</span></div>

                    {{-- Controller validates: 'category_id' required --}}
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

                    {{-- Controller validates: 'unit_id' required --}}
                    <div class="form-group">
                        <label class="form-label">Unit *</label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">Select…</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                    {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
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
// ── Margin preview ────────────────────────────────────────────────
function updateMargin() {
    const buy  = parseFloat(document.querySelector('[name=buying_price]').value)  || 0;
    const sell = parseFloat(document.querySelector('[name=selling_price]').value) || 0;
    const margin    = sell - buy;
    const marginPct = sell > 0 ? (margin / sell * 100) : 0;
    const color = margin >= 0 ? 'var(--success)' : 'var(--danger)';
    document.getElementById('marginVal').textContent  = '$' + margin.toFixed(2);
    document.getElementById('marginPct').textContent  = marginPct.toFixed(1) + '%';
    document.getElementById('marginVal').style.color  = color;
    document.getElementById('marginPct').style.color  = color;
}
document.querySelector('[name=buying_price]').addEventListener('input', updateMargin);
document.querySelector('[name=selling_price]').addEventListener('input', updateMargin);

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
codeInput.addEventListener('input', function () {
    this.dataset.manual = '1';
});
</script>
@endpush