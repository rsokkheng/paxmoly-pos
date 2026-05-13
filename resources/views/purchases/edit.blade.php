@extends('layouts.app')
@section('title', 'Edit Purchase')
@section('page-title', 'Edit — '.$purchase->reference_no)
@section('breadcrumb', 'Purchases / Edit')

@section('topbar-actions')
    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@endsection

@section('content')
<form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
@csrf @method('PUT')

{{-- ══ ORDER DETAILS ══ --}}
<div class="card" style="margin-bottom:16px;padding:16px 20px;">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Supplier *</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">Select supplier…</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}"
                        {{ old('supplier_id', $purchase->supplier_id) == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}{{ $sup->company ? ' — '.$sup->company : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Purchase Date *</label>
            <input type="date" name="purchase_date" class="form-control"
                   value="{{ old('purchase_date', $purchase->purchase_date?->toDateString()) }}" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Status *</label>
            <select name="status" class="form-control" required>
                <option value="pending"  {{ old('status', $purchase->status) === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="received" {{ old('status', $purchase->status) === 'received' ? 'selected' : '' }}>Received (adds stock now)</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control"
                   placeholder="Optional notes…" value="{{ old('notes', $purchase->notes) }}">
        </div>
    </div>
</div>

{{-- ══ ORDER ITEMS ══ --}}
<div class="card" style="padding:0;overflow:hidden;margin-bottom:16px;">

        {{-- Card Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px;">
            <div style="font-size:15px;font-weight:700;color:var(--text);">Order Items</div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                {{-- Global unit toggle --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:11px;color:var(--muted);font-family:var(--mono);">Display & Input Unit</span>
                    <i class="fas fa-info-circle" style="font-size:11px;color:var(--muted);" title="Sets the default buying unit for all new rows"></i>
                    <div style="display:flex;border:1px solid var(--border);border-radius:6px;overflow:hidden;">
                        <button type="button" id="globalCase" onclick="setGlobalUnit('carton')"
                                style="padding:6px 14px;font-size:12px;font-weight:700;border:none;cursor:pointer;transition:all .15s;background:#3b82f6;color:#fff;">
                            CASE
                        </button>
                        <button type="button" id="globalPcs" onclick="setGlobalUnit('piece')"
                                style="padding:6px 14px;font-size:12px;font-weight:700;border:none;cursor:pointer;transition:all .15s;background:var(--surface2);color:var(--muted);">
                            PCS
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="addRow()">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap" id="itemsTableWrap" style="display:none;">
            <table style="border-collapse:collapse;width:100%;">
                <thead>
                    <tr style="background:var(--bg);">
                        <th style="width:36px;padding:10px 8px 10px 20px;text-align:center;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">#</th>
                        <th style="padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">PRODUCT</th>
                        <th style="width:130px;padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">UNIT</th>
                        <th style="width:90px;padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">QTY</th>
                        <th style="width:150px;padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">UNIT PRICE</th>
                        <th style="width:160px;padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">SUBTOTAL</th>
                        <th style="width:120px;padding:10px 14px;font-size:11px;font-family:var(--mono);color:var(--muted);letter-spacing:.06em;border-bottom:1px solid var(--border);">STOCK IMPACT</th>
                        <th style="width:40px;border-bottom:1px solid var(--border);"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        {{-- Empty state --}}
        <div id="noItemsMsg" style="text-align:center;color:var(--muted);padding:48px;font-size:13px;">
            <i class="fas fa-box-open" style="font-size:28px;display:block;margin-bottom:10px;opacity:.25;"></i>
            No items yet — click <strong>Add Item</strong> to begin.
        </div>

        {{-- Add Item row at bottom --}}
        <div id="addRowBtn" style="display:none;padding:12px 20px;border-top:1px dashed var(--border);">
            <button type="button" onclick="addRow()"
                    style="width:100%;padding:10px;border:1px dashed var(--border);border-radius:6px;background:transparent;color:var(--muted);cursor:pointer;font-size:13px;transition:all .15s;"
                    onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)';"
                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)';">
                <i class="fas fa-plus" style="margin-right:6px;"></i> Add Item
            </button>
        </div>
</div>

{{-- ══ SUMMARY BAR ══ --}}
<div id="summaryBar" style="display:none;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;margin-bottom:80px;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;align-items:center;">
        <div style="text-align:center;">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-layer-group" style="margin-right:4px;"></i>Total Items</div>
            <div style="font-size:20px;font-weight:700;font-family:var(--mono);" id="statItems">0</div>
            <div style="font-size:10px;color:var(--muted);">Items</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-boxes" style="margin-right:4px;"></i>Total Cases</div>
            <div style="font-size:20px;font-weight:700;font-family:var(--mono);color:#3b82f6;" id="statCases">0</div>
            <div style="font-size:10px;color:var(--muted);">Cases</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;"><i class="fas fa-barcode" style="margin-right:4px;"></i>Total PCS Equivalent</div>
            <div style="font-size:20px;font-weight:700;font-family:var(--mono);color:var(--accent);" id="statPcs">0</div>
            <div style="font-size:10px;color:var(--muted);">PCS</div>
        </div>
        <div style="border-left:1px solid var(--border);padding-left:16px;">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px;">Grand Total</div>
            <div style="font-size:24px;font-weight:700;font-family:var(--mono);color:var(--text);" id="statGrandCase">$0.00 <span style="font-size:12px;color:var(--muted);">USD</span></div>
        </div>
    </div>
    <div style="margin-top:10px;font-size:10px;color:var(--muted);border-top:1px solid var(--border);padding-top:8px;">
        <i class="fas fa-info-circle"></i> Stock will be recorded in PCS (Base Unit). Grand Total calculated from buying unit prices.
    </div>
</div>

</form>

{{-- ══ STICKY FOOTER: PAYMENT SUMMARY ══ --}}
<div id="payFooter" style="position:fixed;bottom:0;left:0;right:0;z-index:100;background:var(--surface);border-top:2px solid var(--border);padding:12px 24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
    <div style="display:flex;flex-direction:column;">
        <span style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Grand Total</span>
        <span style="font-size:22px;font-weight:700;font-family:var(--mono);color:var(--accent);" id="summaryTotal">$0.00</span>
    </div>
    <div style="width:1px;height:40px;background:var(--border);"></div>
    <div style="display:flex;flex-direction:column;gap:4px;">
        <label style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;" for="paidAmount">Amount Paid</label>
        <input type="number" id="paidAmount" name="_paidProxy" step="0.01" min="0"
               value="{{ old('paid_amount', $purchase->paid_amount) }}"
               oninput="syncPaid(this.value)"
               style="width:140px;background:var(--bg);border:1px solid var(--border);border-radius:5px;padding:6px 10px;color:var(--text);font-size:15px;font-family:var(--mono);outline:none;"
               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
        <input type="hidden" name="paid_amount" id="paidAmountHidden" form="purchaseForm" value="{{ old('paid_amount', $purchase->paid_amount) }}">
    </div>
    <div style="display:flex;flex-direction:column;">
        <span style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Paid</span>
        <span style="font-size:18px;font-weight:700;font-family:var(--mono);color:var(--success);" id="summaryPaid">$0.00</span>
    </div>
    <div style="display:flex;flex-direction:column;">
        <span style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Due</span>
        <span style="font-size:18px;font-weight:700;font-family:var(--mono);" id="summaryDue">$0.00</span>
    </div>
    <div style="margin-left:auto;display:flex;gap:10px;align-items:center;">
        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" form="purchaseForm" class="btn btn-primary" style="padding:10px 28px;font-size:15px;">
            <i class="fas fa-save"></i> Update Purchase Order
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
.pu-toggle { display:flex;border:1px solid var(--border);border-radius:5px;overflow:hidden;width:fit-content; }
.pu-btn {
    padding:4px 12px;font-size:11px;font-weight:700;border:none;cursor:pointer;
    transition:all .15s;background:var(--surface2);color:var(--muted);
}
.pu-btn.active-case { background:#3b82f6;color:#fff; }
.pu-btn.active-pcs  { background:#22c55e;color:#fff; }
.price-main { font-size:16px;font-weight:700;font-family:var(--mono);color:var(--text); }
.price-label { font-size:10px;color:#3b82f6;font-family:var(--mono);margin-top:1px; }
.price-sub   { font-size:12px;color:var(--muted);font-family:var(--mono);margin-top:4px;padding-top:4px;border-top:1px dashed var(--border); }
.stock-impact {
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);
    border-radius:6px;padding:6px 10px;min-width:90px;text-align:center;
}
.stock-impact .qty { font-size:14px;font-weight:700;color:var(--success);font-family:var(--mono); }
.stock-impact .lbl { font-size:10px;color:var(--muted);margin-top:1px; }
.item-row td { padding:14px 14px;border-bottom:1px solid var(--border);vertical-align:middle; }
.item-row:last-child td { border-bottom:none; }
.item-row:hover { background:var(--surface2); }
.prod-img { width:44px;height:44px;object-fit:cover;border-radius:5px;border:1px solid var(--border);flex-shrink:0; }
.prod-img-placeholder { width:44px;height:44px;border-radius:5px;border:1px solid var(--border);
    display:flex;align-items:center;justify-content:center;background:var(--surface2);color:var(--muted);flex-shrink:0; }
.qty-input { width:70px;text-align:center;background:var(--bg);border:1px solid var(--border);
    border-radius:5px;padding:7px 6px;color:var(--text);font-size:14px;font-weight:600;
    font-family:var(--mono);outline:none; }
.qty-input:focus { border-color:var(--accent); }
.cost-input { width:110px;background:var(--bg);border:1px solid var(--border);border-radius:5px;
    padding:7px 10px;color:var(--text);font-size:14px;font-family:var(--mono);outline:none; }
.cost-input:focus { border-color:var(--accent); }
</style>
@endpush

@push('scripts')
<script>
const PRODUCTS   = @json($productsJson);
const EXISTING   = @json($existingJson);
let rowIndex     = 0;
let globalUnit   = 'carton';

function syncPaid(val) {
    document.getElementById('paidAmountHidden').value = val;
    updateSummary();
}

function setGlobalUnit(unit) {
    globalUnit = unit;
    document.getElementById('globalCase').style.background = unit === 'carton' ? '#3b82f6' : 'var(--surface2)';
    document.getElementById('globalCase').style.color      = unit === 'carton' ? '#fff' : 'var(--muted)';
    document.getElementById('globalPcs').style.background  = unit === 'piece'  ? '#22c55e' : 'var(--surface2)';
    document.getElementById('globalPcs').style.color       = unit === 'piece'  ? '#fff' : 'var(--muted)';
    // Only sets the default for NEW rows — existing rows are not changed
}

function productOptions(selectedId) {
    return '<option value="">Select product…</option>'
        + PRODUCTS.map(p =>
            `<option value="${p.id}" ${p.id == selectedId ? 'selected' : ''}>[${p.code}] ${p.name}</option>`
        ).join('');
}

function addRow(existing) {
    const idx  = rowIndex++;
    const body = document.getElementById('itemsBody');
    const tr   = document.createElement('tr');
    tr.className   = 'item-row';
    tr.dataset.idx = idx;

    tr.innerHTML = `
    <td style="text-align:center;padding:14px 8px 14px 20px;color:var(--muted);font-family:var(--mono);font-size:12px;" id="rowNum_${idx}"></td>
    <td style="min-width:220px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div id="prodThumb_${idx}">
                <div class="prod-img-placeholder"><i class="fas fa-box" style="font-size:16px;"></i></div>
            </div>
            <div style="flex:1;">
                <select class="form-control" id="pid_${idx}" name="items[${idx}][product_id]"
                        onchange="onProductChange(this,${idx})" required
                        style="font-size:13px;margin-bottom:6px;">
                    ${productOptions(existing?.product_id)}
                </select>
                <div id="prodMeta_${idx}" style="font-size:11px;color:var(--muted);line-height:1.6;"></div>
            </div>
        </div>
    </td>
    <td>
        <div class="pu-toggle" id="unitToggle_${idx}">
            <button type="button" class="pu-btn" data-unit="piece"
                    onclick="selectUnit(${idx},'piece')" id="pcsBtnRow_${idx}">PCS</button>
            <button type="button" class="pu-btn" data-unit="carton"
                    onclick="selectUnit(${idx},'carton')" id="caseBtnRow_${idx}" style="display:none;">CASE</button>
        </div>
        <div style="font-size:10px;color:#3b82f6;font-family:var(--mono);margin-top:4px;" id="unitDesc_${idx}"></div>
        <input type="hidden" name="items[${idx}][selling_unit]" id="selUnit_${idx}" value="piece">
        <input type="hidden" name="items[${idx}][pack_size]"    id="ps_${idx}"      value="1">
    </td>
    <td>
        <input type="number" class="qty-input" id="qty_${idx}"
               name="items[${idx}][quantity]" min="1" step="1" value="${existing?.quantity ?? 1}"
               oninput="calcRow(${idx})" required>
        <div style="font-size:10px;color:var(--muted);text-align:center;margin-top:3px;" id="qtyLabel_${idx}">pcs</div>
    </td>
    <td>
        <div>
            <input type="number" class="cost-input" id="cost_${idx}"
                   name="items[${idx}][unit_cost]" step="0.01" min="0"
                   value="${existing?.unit_cost != null ? parseFloat(existing.unit_cost).toFixed(2) : ''}"
                   oninput="calcRow(${idx})" required placeholder="0.00">
            <div class="price-label" id="costLabel_${idx}">USD / PCS</div>
        </div>
        <div class="price-sub" id="pcsEquivPrice_${idx}" style="display:none;"></div>
    </td>
    <td>
        <div class="price-main" id="subTotal_${idx}">$0.00</div>
        <div style="font-size:10px;color:var(--muted);font-family:var(--mono);" id="subLabel_${idx}">Total (PCS)</div>
        <div class="price-sub" id="subEquiv_${idx}" style="display:none;"></div>
    </td>
    <td>
        <div class="stock-impact" id="stockImpact_${idx}">
            <i class="fas fa-box-open" style="font-size:16px;color:var(--muted);opacity:.4;"></i>
            <div class="lbl">No product</div>
        </div>
    </td>
    <td style="padding-right:12px;">
        <button type="button" onclick="removeRow(this)"
                style="width:32px;height:32px;border-radius:5px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);color:var(--danger);cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-trash-alt" style="font-size:12px;"></i>
        </button>
    </td>`;

    body.appendChild(tr);
    toggleTable();
    reindex();

    if (existing?.product_id) {
        const p = PRODUCTS.find(x => x.id == existing.product_id);
        if (p) applyProduct(idx, p, existing.selling_unit ?? globalUnit, existing.pack_size);
    }
    if (existing) calcRow(idx);
}

function applyProduct(idx, product, unit, packSizeOverride) {
    const thumb = document.getElementById('prodThumb_' + idx);
    thumb.innerHTML = product.image
        ? `<img src="${product.image}" class="prod-img" alt="${product.name}">`
        : `<div class="prod-img-placeholder"><i class="fas fa-box" style="font-size:16px;"></i></div>`;

    const meta = document.getElementById('prodMeta_' + idx);
    const caseInfo = product.has_case ? `<span style="color:var(--muted);">1 case = ${product.case_uom} pcs</span>` : '';
    const badge = product.is_active
        ? `<span style="background:rgba(34,197,94,.15);color:var(--success);font-size:10px;padding:1px 6px;border-radius:3px;font-family:var(--mono);">Active</span>`
        : `<span style="background:rgba(239,68,68,.15);color:var(--danger);font-size:10px;padding:1px 6px;border-radius:3px;">Inactive</span>`;
    meta.innerHTML = `${caseInfo ? caseInfo + ' &nbsp; ' : ''}${badge}`;

    const caseBtn = document.getElementById('caseBtnRow_' + idx);
    if (product.has_case) {
        caseBtn.style.display = '';
    } else {
        caseBtn.style.display = 'none';
        unit = 'piece';
    }

    const resolvedUnit = (unit === 'carton' && product.has_case) ? 'carton' : 'piece';
    const packSize = packSizeOverride ?? (resolvedUnit === 'carton' ? product.case_uom : 1);

    document.getElementById('selUnit_' + idx).value = resolvedUnit;
    document.getElementById('ps_' + idx).value      = packSize;

    ['piece','carton'].forEach(u => {
        const btn = document.querySelector(`#unitToggle_${idx} [data-unit="${u}"]`);
        if (btn) { btn.classList.remove('active-case','active-pcs'); }
    });
    const activeBtn = document.querySelector(`#unitToggle_${idx} [data-unit="${resolvedUnit}"]`);
    if (activeBtn) activeBtn.classList.add(resolvedUnit === 'carton' ? 'active-case' : 'active-pcs');

    const desc = document.getElementById('unitDesc_' + idx);
    desc.innerHTML = resolvedUnit === 'carton' && product.has_case
        ? `<i class="fas fa-sync-alt" style="margin-right:3px;font-size:9px;"></i>1 case = ${packSize} pcs`
        : '';

    document.getElementById('qtyLabel_' + idx).textContent = resolvedUnit === 'carton' ? 'case' : 'pcs';
    document.getElementById('costLabel_' + idx).textContent = resolvedUnit === 'carton' ? 'USD / CASE' : 'USD / PCS';
    document.getElementById('costLabel_' + idx).style.color  = resolvedUnit === 'carton' ? '#3b82f6' : '#22c55e';

    const costEl = document.getElementById('cost_' + idx);
    if (!costEl.value || parseFloat(costEl.value) === 0) {
        costEl.value = resolvedUnit === 'carton'
            ? (product.buying_price * packSize).toFixed(2)
            : product.buying_price.toFixed(2);
    }

    calcRow(idx);
}

function onProductChange(select, idx) {
    const product = PRODUCTS.find(p => p.id == select.value);
    if (!product) {
        document.getElementById('prodMeta_' + idx).innerHTML = '';
        document.getElementById('prodThumb_' + idx).innerHTML = `<div class="prod-img-placeholder"><i class="fas fa-box" style="font-size:16px;"></i></div>`;
        return;
    }
    document.getElementById('cost_' + idx).value = '';
    // Keep the unit the user already selected for this row; only fall back to globalUnit if none set yet
    const rowUnit = document.getElementById('selUnit_' + idx)?.value || globalUnit;
    applyProduct(idx, product, rowUnit, null);
}

function selectUnit(idx, unit) {
    const pid = document.getElementById('pid_' + idx)?.value;
    const product = PRODUCTS.find(p => p.id == pid);
    if (!product) return;
    document.getElementById('cost_' + idx).value = '';
    applyProduct(idx, product, unit, null);
}

function calcRow(idx) {
    const qty      = parseFloat(document.getElementById('qty_' + idx)?.value)  || 0;
    const cost     = parseFloat(document.getElementById('cost_' + idx)?.value) || 0;
    const unit     = document.getElementById('selUnit_' + idx)?.value ?? 'piece';
    const packSize = parseInt(document.getElementById('ps_' + idx)?.value)     || 1;
    const pid      = document.getElementById('pid_' + idx)?.value;

    const lineTotal   = qty * cost;
    const piecesAdded = unit === 'carton' ? qty * packSize : qty;
    const pcsCost     = unit === 'carton' && packSize > 1 ? cost / packSize : cost;

    document.getElementById('subTotal_' + idx).textContent = '$' + lineTotal.toFixed(2);
    document.getElementById('subLabel_' + idx).textContent  = unit === 'carton' ? 'Total (CASE)' : 'Total (PCS)';

    const subEquiv = document.getElementById('subEquiv_' + idx);
    if (unit === 'carton' && packSize > 1) {
        subEquiv.style.display = '';
        subEquiv.textContent   = '$' + lineTotal.toFixed(2) + ' Total (PCS Equivalent)';
    } else {
        subEquiv.style.display = 'none';
    }

    const pcsEquivEl = document.getElementById('pcsEquivPrice_' + idx);
    if (unit === 'carton' && packSize > 1) {
        pcsEquivEl.style.display = '';
        pcsEquivEl.textContent   = pcsCost.toFixed(2) + ' USD / PCS';
    } else {
        pcsEquivEl.style.display = 'none';
    }

    const si = document.getElementById('stockImpact_' + idx);
    if (pid && qty > 0) {
        si.innerHTML = `
            <i class="fas fa-box" style="font-size:14px;color:var(--success);"></i>
            <div class="qty">+${piecesAdded} pcs</div>
            <div class="lbl">To Stock</div>`;
    } else {
        si.innerHTML = `<i class="fas fa-box-open" style="font-size:16px;color:var(--muted);opacity:.4;"></i><div class="lbl">No product</div>`;
    }

    updateSummary();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    toggleTable();
    reindex();
    updateSummary();
}

function reindex() {
    document.querySelectorAll('#itemsBody tr.item-row').forEach((tr, i) => {
        const el = document.getElementById('rowNum_' + tr.dataset.idx);
        if (el) el.textContent = i + 1;
    });
}

function toggleTable() {
    const hasRows = document.getElementById('itemsBody').children.length > 0;
    document.getElementById('itemsTableWrap').style.display = hasRows ? '' : 'none';
    document.getElementById('noItemsMsg').style.display     = hasRows ? 'none' : '';
    document.getElementById('addRowBtn').style.display      = hasRows ? '' : 'none';
    document.getElementById('summaryBar').style.display     = hasRows ? '' : 'none';
}

function updateSummary() {
    let grand = 0, totalCases = 0, totalPcs = 0, totalItems = 0;

    document.querySelectorAll('#itemsBody tr.item-row').forEach(tr => {
        const i        = tr.dataset.idx;
        const qty      = parseFloat(document.getElementById('qty_' + i)?.value)  || 0;
        const cost     = parseFloat(document.getElementById('cost_' + i)?.value) || 0;
        const unit     = document.getElementById('selUnit_' + i)?.value ?? 'piece';
        const packSize = parseInt(document.getElementById('ps_' + i)?.value)     || 1;
        const pid      = document.getElementById('pid_' + i)?.value;

        if (!pid) return;
        totalItems++;
        grand += qty * cost;

        if (unit === 'carton') {
            totalCases += qty;
            totalPcs   += qty * packSize;
        } else {
            totalPcs += qty;
        }
    });

    const paid = Math.min(parseFloat(document.getElementById('paidAmount')?.value) || 0, grand);

    document.getElementById('statItems').textContent   = totalItems;
    document.getElementById('statCases').textContent   = totalCases;
    document.getElementById('statPcs').textContent     = totalPcs;
    document.getElementById('statGrandCase').innerHTML = '$' + grand.toFixed(2) + ' <span style="font-size:12px;color:var(--muted);">USD</span>';

    document.getElementById('summaryTotal').textContent = '$' + grand.toFixed(2);
    document.getElementById('summaryPaid').textContent  = '$' + paid.toFixed(2);
    document.getElementById('summaryDue').textContent   = '$' + (grand - paid).toFixed(2);
    document.getElementById('summaryDue').style.color   = (grand - paid) > 0 ? 'var(--danger)' : 'var(--success)';
}

document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#itemsBody tr.item-row');
    if (!rows.length) { e.preventDefault(); alert('Please add at least one item.'); return; }

    let valid = true;
    rows.forEach(tr => {
        const i = tr.dataset.idx;
        const cost = document.getElementById('cost_' + i);
        const qty  = document.getElementById('qty_' + i);
        if (!cost?.value || parseFloat(cost.value) <= 0) { valid = false; cost?.classList.add('is-invalid'); }
        else cost?.classList.remove('is-invalid');
        if (!qty?.value || parseInt(qty.value) < 1) { valid = false; qty?.classList.add('is-invalid'); }
        else qty?.classList.remove('is-invalid');
    });
    if (!valid) { e.preventDefault(); alert('Please fill in all quantities and unit costs.'); return; }

    rows.forEach((tr, newIdx) => {
        const oldIdx = tr.dataset.idx;
        tr.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(`items[${oldIdx}]`, `items[${newIdx}]`);
        });
        tr.dataset.idx = newIdx;
    });
});

// Load existing items on page load
EXISTING.length ? EXISTING.forEach(item => addRow(item)) : addRow();
</script>
@endpush
