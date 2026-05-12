@extends('layouts.app')
@section('title', $productSet->name)
@section('page-title', $productSet->name)
@section('breadcrumb', 'Products / Sets / View')

@section('topbar-actions')
    <a href="{{ route('product-sets.edit', $productSet) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
    <a href="{{ route('product-sets.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
@endsection

@section('content')
@php $avail = $productSet->availableQty(); @endphp

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start;">

    <div style="display:flex;flex-direction:column;gap:16px;">
        {{-- Set info --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Set Information</span>
                <span class="badge {{ $productSet->is_active ? 'badge-success' : 'badge-secondary' }}">
                    {{ $productSet->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div style="padding:16px 20px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Set Name</div>
                    <div style="font-weight:600;">{{ $productSet->name }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Code / SKU</div>
                    <div class="td-mono">{{ $productSet->code }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Selling Price</div>
                    <div class="mono" style="font-size:18px;font-weight:700;color:var(--accent);">${{ number_format($productSet->selling_price, 2) }}</div>
                </div>
                <div>
                    <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Estimated Cost</div>
                    <div class="mono" style="font-weight:600;">${{ number_format($productSet->totalCost(), 2) }}</div>
                </div>
                @if($productSet->description)
                <div style="grid-column:1/-1;">
                    <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Description</div>
                    <div style="font-size:13px;">{{ $productSet->description }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Components --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Components ({{ $productSet->items->count() }} products)</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Unit</th>
                            <th>Qty per Set</th>
                            <th>In Stock</th>
                            <th>Sets Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productSet->items as $item)
                        @php
                            $stock     = (int) $item->product->stock_quantity;
                            $unitPieces= $item->unit_type === 'carton'
                                ? $item->quantity * ($item->product->productUnits->firstWhere('unit_type','carton')?->uom ?? 1)
                                : $item->quantity;
                            $setsAvail = $unitPieces > 0 ? floor($stock / $unitPieces) : 0;
                        @endphp
                        <tr>
                            <td style="font-weight:500;">{{ $item->product->name }}</td>
                            <td class="td-mono">{{ $item->product->code }}</td>
                            <td><span class="badge badge-secondary">{{ strtoupper($item->unit_type) }}</span></td>
                            <td class="mono">{{ $item->quantity }}</td>
                            <td class="mono {{ $stock <= 0 ? 'text-danger' : '' }}">{{ $stock }}</td>
                            <td>
                                <span class="badge {{ $setsAvail <= 0 ? 'badge-danger' : ($setsAvail <= 5 ? 'badge-warn' : 'badge-success') }}">
                                    {{ $setsAvail }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        @if($productSet->image)
        <div class="card">
            <img src="{{ asset('storage/'.$productSet->image) }}" style="width:100%;border-radius:var(--radius);display:block;">
        </div>
        @endif

        <div class="card">
            <div class="card-header"><span class="card-title">Stock Status</span></div>
            <div style="padding:20px;text-align:center;">
                <div style="font-size:40px;font-weight:700;font-family:var(--mono);color:{{ $avail <= 0 ? 'var(--danger)' : ($avail <= 5 ? '#f0b429' : 'var(--success)') }};">
                    {{ $avail }}
                </div>
                <div style="font-size:13px;color:var(--muted);margin-top:4px;">sets available</div>
            </div>
        </div>

        <div class="card">
            <div style="padding:16px;display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('product-sets.edit', $productSet) }}" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fas fa-edit"></i> Edit Set
                </a>
                <form method="POST" action="{{ route('product-sets.toggle', $productSet) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">
                        <i class="fas fa-{{ $productSet->is_active ? 'eye-slash' : 'eye' }}"></i>
                        {{ $productSet->is_active ? 'Disable' : 'Enable' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('product-sets.destroy', $productSet) }}" onsubmit="return confirm('Delete this set?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;color:var(--danger);">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
