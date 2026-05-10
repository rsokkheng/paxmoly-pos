@extends('layouts.app')
@section('title', 'Edit Purchase')
@section('page-title', 'Edit — '.$purchase->reference_no)
@section('breadcrumb', 'Purchases / Edit')

@section('content')
<form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    {{-- LEFT: Items --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Order Items</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>

            <div class="table-wrap" id="itemsTableWrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="width:90px;">Qty</th>
                            <th style="width:140px;">Unit Cost ($)</th>
                            <th style="width:120px;">Subtotal</th>
                            <th style="width:36px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
            <div id="noItemsMsg" style="display:none;text-align:center;color:var(--muted);padding:32px;font-size:13px;">
                No items — click <strong>Add Item</strong>.
            </div>
        </div>
    </div>

    {{-- RIGHT: Details --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Order Details</span></div>

            <div class="form-group">
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

            <div class="form-group">
                <label class="form-label">Purchase Date *</label>
                <input type="date" name="purchase_date" class="form-control"
                       value="{{ old('purchase_date', $purchase->purchase_date?->toDateString()) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Status *</label>
                <select name="status" class="form-control" required>
                    <option value="pending"  {{ old('status', $purchase->status) === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="received" {{ old('status', $purchase->status) === 'received' ? 'selected' : '' }}>Received (adds stock)</option>
                </select>
                <div class="form-text">Changing to <em>Received</em> will add stock.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Amount Paid ($)</label>
                <input type="number" name="paid_amount" id="paidAmount"
                       class="form-control" step="0.01" min="0"
                       value="{{ old('paid_amount', $purchase->paid_amount) }}"
                       oninput="updateSummary()">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
            </div>
        </div>

        {{-- Summary --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Order Summary</span></div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;">
                    <span>Grand Total</span>
                    <span class="mono text-accent" id="summaryTotal">$0.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;padding-top:8px;border-top:1px solid var(--border);">
                    <span class="text-muted">Paid</span>
                    <span class="mono text-success" id="summaryPaid">$0.00</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span class="text-muted">Due</span>
                    <span class="mono" id="summaryDue" style="color:var(--danger);">$0.00</span>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:15px;">
            <i class="fas fa-save"></i> Update Purchase Order
        </button>
        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary"
           style="width:100%;text-align:center;">Cancel</a>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
const PRODUCTS = @json($productsJson);
const EXISTING = @json($existingJson);

let rowIndex = 0;

function productOptions(selectedId) {
    return '<option value="">Select product…</option>'
        + PRODUCTS.map(p =>
            `<option value="${p.id}" data-price="${p.price}"
                ${p.id == selectedId ? 'selected' : ''}>
                [${p.code}] ${p.name} (${p.unit})
            </option>`
        ).join('');
}

function addRow(existing) {
    const idx  = rowIndex++;
    const body = document.getElementById('itemsBody');
    const tr   = document.createElement('tr');
    tr.dataset.idx = idx;

    const qty  = existing?.quantity  ?? 1;
    const cost = existing?.unit_cost != null ? parseFloat(existing.unit_cost).toFixed(2) : '';

    tr.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="form-control"
                    onchange="onProductChange(this, ${idx})" required style="min-width:180px;">
                ${productOptions(existing?.product_id)}
            </select>
        </td>
        <td>
            <input type="number" name="items[${idx}][quantity]" id="qty_${idx}"
                   class="form-control" min="1" value="${qty}"
                   oninput="calcRow(${idx})" required>
        </td>
        <td>
            <input type="number" name="items[${idx}][unit_cost]" id="cost_${idx}"
                   class="form-control" step="0.01" min="0" value="${cost}"
                   oninput="calcRow(${idx})" required>
        </td>
        <td>
            <span id="sub_${idx}" class="mono" style="font-size:13px;font-weight:600;">$0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="removeRow(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>`;

    body.appendChild(tr);
    toggleTable();
    if (existing) calcRow(idx);
}

function onProductChange(select, idx) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;
    document.getElementById(`cost_${idx}`).value = parseFloat(opt.dataset.price).toFixed(2);
    calcRow(idx);
}

function calcRow(idx) {
    const qty  = parseFloat(document.getElementById(`qty_${idx}`)?.value)  || 0;
    const cost = parseFloat(document.getElementById(`cost_${idx}`)?.value) || 0;
    const el   = document.getElementById(`sub_${idx}`);
    if (el) el.textContent = '$' + (qty * cost).toFixed(2);
    updateSummary();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    toggleTable();
    updateSummary();
}

function toggleTable() {
    const hasRows = document.getElementById('itemsBody').children.length > 0;
    document.getElementById('itemsTableWrap').style.display = hasRows ? ''     : 'none';
    document.getElementById('noItemsMsg').style.display     = hasRows ? 'none' : '';
}

function updateSummary() {
    let grand = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const i = tr.dataset.idx;
        grand += (parseFloat(document.getElementById(`qty_${i}`)?.value)  || 0)
               * (parseFloat(document.getElementById(`cost_${i}`)?.value) || 0);
    });
    const paid = Math.min(parseFloat(document.getElementById('paidAmount').value) || 0, grand);
    const due  = grand - paid;

    document.getElementById('summaryTotal').textContent = '$' + grand.toFixed(2);
    document.getElementById('summaryPaid').textContent  = '$' + paid.toFixed(2);
    document.getElementById('summaryDue').textContent   = '$' + due.toFixed(2);
    document.getElementById('summaryDue').style.color   = due > 0 ? 'var(--danger)' : 'var(--success)';
}

// Reindex all rows sequentially before submit
document.getElementById('purchaseForm').addEventListener('submit', function (e) {
    const rows = document.querySelectorAll('#itemsBody tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
        return;
    }
    let valid = true;
    rows.forEach(tr => {
        const i    = tr.dataset.idx;
        const cost = document.getElementById(`cost_${i}`);
        const qty  = document.getElementById(`qty_${i}`);
        if (!cost?.value || parseFloat(cost.value) <= 0) { valid = false; cost?.classList.add('is-invalid'); }
        else cost?.classList.remove('is-invalid');
        if (!qty?.value  || parseInt(qty.value)   < 1)  { valid = false; qty?.classList.add('is-invalid'); }
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