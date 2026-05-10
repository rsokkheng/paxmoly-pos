@extends('layouts.app')
@section('title', 'Inventory Adjustment')
@section('page-title', 'Inventory Adjustment')
@section('breadcrumb', 'Inventory / Adjustment')

@section('topbar-actions')
    <a href="{{ route('inventory.movements') }}" class="btn btn-secondary">
        <i class="fas fa-list"></i> View Movement Log
    </a>
@endsection

@section('content')

<div style="max-width:680px;">

<div class="card" style="margin-bottom:16px;padding:16px 20px;background:rgba(240,180,41,0.06);border-color:rgba(240,180,41,0.25);">
    <div style="display:flex;gap:12px;align-items:flex-start;">
        <i class="fas fa-info-circle" style="color:var(--accent);margin-top:2px;"></i>
        <div style="font-size:13px;color:var(--muted);line-height:1.6;">
            Use adjustments to correct stock discrepancies — damaged goods, theft, counting errors, or
            opening stock entries. Every adjustment is logged with your user account and timestamp.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">New Stock Adjustment</span>
    </div>

    <form method="POST" action="{{ route('inventory.adjustments.store') }}">
        @csrf

        <!-- Product -->
        <div class="form-group">
            <label class="form-label">Product <span style="color:var(--danger);">*</span></label>
            <select name="product_id" id="product_id" class="form-control" required onchange="updateStock(this)">
                <option value="">— Select product —</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}"
                            data-stock="{{ $p->stock_quantity }}"
                            {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}  ·  {{ $p->code }}  ·  {{ $p->stock_quantity }} in stock
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <div class="form-text" style="color:var(--danger);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Current stock display -->
        <div id="stock-display" style="display:none;margin-bottom:18px;">
            <div class="card" style="padding:14px 18px;background:var(--bg);display:flex;align-items:center;gap:16px;">
                <i class="fas fa-boxes" style="color:var(--muted);font-size:18px;"></i>
                <div>
                    <div style="font-size:11px;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.06em;">Current Stock</div>
                    <div id="current-stock-val" style="font-size:22px;font-weight:700;font-family:var(--mono);color:var(--text);"></div>
                </div>
                <div style="margin-left:auto;">
                    <div style="font-size:11px;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:.06em;">After Adjustment</div>
                    <div id="after-stock-val" style="font-size:22px;font-weight:700;font-family:var(--mono);color:var(--accent);">—</div>
                </div>
            </div>
        </div>

        <!-- Adjustment Type -->
        <div class="form-group">
            <label class="form-label">Adjustment Type <span style="color:var(--danger);">*</span></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <label style="cursor:pointer;">
                    <input type="radio" name="adjustment_type" value="add"
                           {{ old('adjustment_type', 'add') === 'add' ? 'checked' : '' }}
                           onchange="recalc()" style="display:none;" id="type-add">
                    <div class="adj-type-card" for="type-add" data-value="add"
                         style="border:2px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center;transition:all 0.15s;">
                        <i class="fas fa-plus-circle" style="font-size:22px;color:var(--success);margin-bottom:8px;display:block;"></i>
                        <div style="font-weight:600;font-size:13px;">Add Stock</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:3px;">Opening stock, found items, corrections</div>
                    </div>
                </label>
                <label style="cursor:pointer;">
                    <input type="radio" name="adjustment_type" value="remove"
                           {{ old('adjustment_type') === 'remove' ? 'checked' : '' }}
                           onchange="recalc()" style="display:none;" id="type-remove">
                    <div class="adj-type-card" for="type-remove" data-value="remove"
                         style="border:2px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center;transition:all 0.15s;">
                        <i class="fas fa-minus-circle" style="font-size:22px;color:var(--danger);margin-bottom:8px;display:block;"></i>
                        <div style="font-weight:600;font-size:13px;">Remove Stock</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:3px;">Damage, theft, expiry, shrinkage</div>
                    </div>
                </label>
            </div>
            @error('adjustment_type')
                <div class="form-text" style="color:var(--danger);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Quantity -->
        <div class="form-group">
            <label class="form-label">Quantity <span style="color:var(--danger);">*</span></label>
            <input type="number" name="quantity" id="adj-qty" class="form-control"
                   min="1" value="{{ old('quantity', 1) }}"
                   oninput="recalc()" required>
            @error('quantity')
                <div class="form-text" style="color:var(--danger);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Reason -->
        <div class="form-group">
            <label class="form-label">Reason <span style="color:var(--danger);">*</span></label>
            <select name="reason" class="form-control" required>
                <option value="">— Select reason —</option>
                <optgroup label="Stock In">
                    <option value="Opening Stock"       {{ old('reason') === 'Opening Stock'       ? 'selected' : '' }}>Opening Stock</option>
                    <option value="Count Correction +"  {{ old('reason') === 'Count Correction +'  ? 'selected' : '' }}>Count Correction (surplus)</option>
                    <option value="Returned from Customer" {{ old('reason') === 'Returned from Customer' ? 'selected' : '' }}>Returned from Customer</option>
                    <option value="Production"          {{ old('reason') === 'Production'          ? 'selected' : '' }}>Production / Assembly</option>
                    <option value="Other Addition"      {{ old('reason') === 'Other Addition'      ? 'selected' : '' }}>Other Addition</option>
                </optgroup>
                <optgroup label="Stock Out">
                    <option value="Damaged"             {{ old('reason') === 'Damaged'             ? 'selected' : '' }}>Damaged / Broken</option>
                    <option value="Expired"             {{ old('reason') === 'Expired'             ? 'selected' : '' }}>Expired</option>
                    <option value="Theft / Loss"        {{ old('reason') === 'Theft / Loss'        ? 'selected' : '' }}>Theft / Loss</option>
                    <option value="Count Correction -"  {{ old('reason') === 'Count Correction -'  ? 'selected' : '' }}>Count Correction (shortage)</option>
                    <option value="Internal Use"        {{ old('reason') === 'Internal Use'        ? 'selected' : '' }}>Internal Use / Consumed</option>
                    <option value="Other Removal"       {{ old('reason') === 'Other Removal'       ? 'selected' : '' }}>Other Removal</option>
                </optgroup>
            </select>
            @error('reason')
                <div class="form-text" style="color:var(--danger);">{{ $message }}</div>
            @enderror
        </div>

        <!-- Notes -->
        <div class="form-group">
            <label class="form-label">Notes <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="Additional details about this adjustment...">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="form-text" style="color:var(--danger);">{{ $message }}</div>
            @enderror
        </div>

        <div style="display:flex;gap:10px;margin-top:4px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save Adjustment</button>
            <a href="{{ route('inventory.movements') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</div>
@endsection

@push('scripts')
<script>
    let currentStock = null;

    function updateStock(sel) {
        const opt = sel.options[sel.selectedIndex];
        currentStock = opt.dataset.stock !== undefined ? parseInt(opt.dataset.stock) : null;
        const display = document.getElementById('stock-display');
        if (currentStock !== null && sel.value) {
            document.getElementById('current-stock-val').textContent = currentStock;
            display.style.display = 'block';
        } else {
            display.style.display = 'none';
        }
        recalc();
    }

    function recalc() {
        if (currentStock === null) return;
        const isAdd = document.getElementById('type-add').checked;
        const qty   = parseInt(document.getElementById('adj-qty').value) || 0;
        const after = isAdd ? currentStock + qty : currentStock - qty;
        const el    = document.getElementById('after-stock-val');
        el.textContent = after;
        el.style.color = after < 0 ? 'var(--danger)' : (isAdd ? 'var(--success)' : 'var(--accent)');
    }

    // Highlight selected adjustment type cards
    function syncCards() {
        document.querySelectorAll('.adj-type-card').forEach(card => {
            const val   = card.dataset.value;
            const radio = document.getElementById('type-' + val);
            card.style.borderColor    = radio.checked ? (val === 'add' ? 'var(--success)' : 'var(--danger)') : 'var(--border)';
            card.style.background     = radio.checked ? (val === 'add' ? 'rgba(34,197,94,.06)' : 'rgba(239,68,68,.06)') : '';
        });
    }

    document.querySelectorAll('input[name="adjustment_type"]').forEach(r => {
        r.addEventListener('change', () => { syncCards(); recalc(); });
    });

    document.querySelectorAll('.adj-type-card').forEach(card => {
        card.addEventListener('click', () => {
            const radio = document.getElementById('type-' + card.dataset.value);
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });

    // Init
    syncCards();
    const preselect = document.getElementById('product_id');
    if (preselect.value) updateStock(preselect);
</script>
@endpush
