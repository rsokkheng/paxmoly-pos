@extends('layouts.app')
@section('title', 'Edit Product Set')
@section('page-title', 'Edit: ' . $productSet->name)
@section('breadcrumb', 'Products / Sets / Edit')

@section('content')
<form method="POST" action="{{ route('product-sets.update', $productSet) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start;">

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Set Details</span></div>
            <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Set Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $productSet->name) }}" required>
                    @error('name')<div style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Code / SKU <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $productSet->code) }}" required style="text-transform:uppercase;">
                    @error('code')<div style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Set Selling Price ($) <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $productSet->selling_price) }}" min="0" step="0.01" required>
                    @error('selling_price')<div style="color:var(--danger);font-size:11px;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $productSet->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">Component Products</span>
                <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
            <div style="padding:16px;">
                <div id="itemsContainer" style="display:flex;flex-direction:column;gap:10px;"></div>
                @error('items')<div style="color:var(--danger);font-size:11px;margin-top:8px;">{{ $message }}</div>@enderror
            </div>
            <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-size:13px;">
                <span style="color:var(--muted);">Estimated Component Cost</span>
                <span class="mono" id="costSummary" style="font-weight:600;">$0.00</span>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Image</span></div>
            <div style="padding:16px;">
                <div id="imgPreviewWrap" style="width:100%;height:160px;background:var(--surface2);border:2px dashed var(--border);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;margin-bottom:12px;overflow:hidden;cursor:pointer;" onclick="document.getElementById('imageInput').click()">
                    @if($productSet->image)
                    <img id="imgPreview" src="{{ asset('storage/'.$productSet->image) }}" style="width:100%;height:100%;object-fit:cover;">
                    <div id="imgPlaceholder" style="display:none;"></div>
                    @else
                    <img id="imgPreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;">
                    <div id="imgPlaceholder" style="text-align:center;color:var(--muted);">
                        <i class="fas fa-image" style="font-size:28px;display:block;margin-bottom:6px;"></i>
                        <span style="font-size:12px;">Click to upload</span>
                    </div>
                    @endif
                </div>
                <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;" onchange="previewImg(this)">
                <button type="button" class="btn btn-secondary" style="width:100%;font-size:12px;" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-upload"></i> Change Image
                </button>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Status</span></div>
            <div style="padding:16px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ $productSet->is_active ? 'checked' : '' }} style="width:16px;height:16px;">
                    <span style="font-size:13px;">Active (visible in POS)</span>
                </label>
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Save Changes</button>
            <a href="{{ route('product-sets.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
@php
$productsJson = $products->map(function($p) {
    return [
        'id'    => $p->id,
        'name'  => $p->name,
        'code'  => $p->code,
        'units' => $p->productUnits->where('is_active', true)->values()->map(function($u) {
            return [
                'type'         => $u->unit_type,
                'label'        => $u->label,
                'buying_price' => (float) $u->buying_price,
            ];
        })->values(),
    ];
});
$existingItemsJson = $productSet->items->map(function($item) {
    return [
        'product_id' => $item->product_id,
        'unit_type'  => $item->unit_type,
        'quantity'   => $item->quantity,
    ];
});
@endphp
<script>
const PRODUCTS = @json($productsJson);

let itemIdx = 0;

function addItem(productId, unitType, qty) {
    const idx = itemIdx++;
    const row = document.createElement('div');
    row.className = 'set-item-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 110px 80px 36px;gap:8px;align-items:center;background:var(--surface2);padding:10px 12px;border-radius:var(--radius);border:1px solid var(--border);';
    row.innerHTML = `
        <div>
            <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:3px;">Product</label>
            <select name="items[${idx}][product_id]" class="form-control item-product" onchange="onProductChange(this)" required>
                <option value="">— Select product —</option>
                ${PRODUCTS.map(p => `<option value="${p.id}" data-units='${JSON.stringify(p.units)}' ${p.id == productId ? 'selected' : ''}>${p.name} (${p.code})</option>`).join('')}
            </select>
        </div>
        <div>
            <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:3px;">Unit</label>
            <select name="items[${idx}][unit_type]" class="form-control item-unit" onchange="recalcCost()" required>
                <option value="piece">Piece</option>
                <option value="carton">Carton</option>
            </select>
        </div>
        <div>
            <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:3px;">Qty</label>
            <input type="number" name="items[${idx}][quantity]" class="form-control item-qty" min="1" value="${qty||1}" onchange="recalcCost()" required>
        </div>
        <button type="button" onclick="this.closest('.set-item-row').remove();recalcCost();"
                style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:16px;padding-top:16px;">
            <i class="fas fa-times"></i>
        </button>`;
    document.getElementById('itemsContainer').appendChild(row);

    if (productId) {
        const sel = row.querySelector('.item-product');
        sel.value = productId;
        onProductChange(sel);
        if (unitType) row.querySelector('.item-unit').value = unitType;
    }
    recalcCost();
}

function onProductChange(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const units = JSON.parse(opt.dataset.units || '[]');
    const row   = sel.closest('.set-item-row');
    const uSel  = row.querySelector('.item-unit');
    const curr  = uSel.value;
    uSel.innerHTML = units.map(u =>
        `<option value="${u.type}" ${u.type===curr?'selected':''}>${u.label} (${u.type})</option>`
    ).join('') || '<option value="piece">Piece</option>';
    recalcCost();
}

function recalcCost() {
    let total = 0;
    document.querySelectorAll('.set-item-row').forEach(function(row) {
        const pSel  = row.querySelector('.item-product');
        const uSel  = row.querySelector('.item-unit');
        const qty   = parseInt(row.querySelector('.item-qty').value) || 0;
        const opt   = pSel.options[pSel.selectedIndex];
        const units = JSON.parse(opt ? (opt.dataset.units || '[]') : '[]');
        const unit  = units.find(u => u.type === uSel.value);
        if (unit) total += unit.buying_price * qty;
    });
    document.getElementById('costSummary').textContent = '$' + total.toFixed(2);
}

function previewImg(input) {
    if (!input.files[0]) return;
    const url = URL.createObjectURL(input.files[0]);
    document.getElementById('imgPreview').src = url;
    document.getElementById('imgPreview').style.display = 'block';
    document.getElementById('imgPlaceholder').style.display = 'none';
}

// Load existing items
const EXISTING_ITEMS = @json($existingItemsJson);
EXISTING_ITEMS.forEach(function(item) {
    addItem(item.product_id, item.unit_type, item.quantity);
});
</script>
@endpush
