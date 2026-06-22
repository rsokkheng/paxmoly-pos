@extends('layouts.app')
@section('title', $product->name_en)
@section('page-title', $product->name_en)
@section('breadcrumb', 'Products / Detail')

@section('topbar-actions')
    <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">
        <i class="fas fa-pencil"></i> Edit
    </a>
    <form action="{{ route('products.destroy', $product) }}" method="POST"
          onsubmit="return confirm('Delete {{ addslashes($product->name_en) }}?')" style="display:inline;">
        @csrf @method('DELETE')
        <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
    </form>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;">

    <!-- Left: details + stock movements -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Product Details</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                @foreach([
                    ['Name (EN)', $product->name_en],
                    ['Name (KH)', $product->name_kh ?? '—'],
                    ['Code',     $product->code],
                    ['Brand',    $product->brand->name ?? $product->brand_name ?? '—'],
                    ['Barcode',  $product->barcode ?? '—'],
                    ['Category', $product->category->name ?? '—'],
                    ['Unit',     $product->unit->name ?? '—'],
                    ['Packing',  $product->packing ?? '—'],
                    ['Tax',      $product->tax ? $product->tax->name.' ('.$product->tax->rate.'%)' : 'None'],
                    ['Status',   $product->is_active ? 'Active' : 'Inactive'],
                ] as [$label, $val])
                <div>
                    <div class="form-label">{{ $label }}</div>
                    <div style="font-size:14px;">{{ $val }}</div>
                </div>
                @endforeach
            </div>

            @if($product->description)
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                <div class="form-label">Description</div>
                <div style="font-size:14px;color:var(--muted);line-height:1.6;">
                    {{ $product->description }}
                </div>
            </div>
            @endif
        </div>

        <!-- Stock Movements -->
        @if(isset($stockMovements) && $stockMovements->count())
        <div class="card">
            <div class="card-header">
                <span class="card-title">Stock Movements</span>
                <span class="badge badge-info">Last 20</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Reference</th>
                            <th>By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockMovements as $mov)
                        <tr>
                            <td>
                                <span class="badge {{
                                    $mov->type === 'sale'        ? 'badge-danger'  :
                                    ($mov->type === 'return'     ? 'badge-success' :
                                    ($mov->type === 'purchase'   ? 'badge-info'    : 'badge-warn'))
                                }}">
                                    {{ ucfirst($mov->type) }}
                                </span>
                            </td>
                            <td class="mono {{ $mov->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                            </td>
                            <td class="td-mono">{{ $mov->before_quantity }}</td>
                            <td class="td-mono">{{ $mov->after_quantity }}</td>
                            <td class="td-mono">{{ $mov->reference ?? '—' }}</td>
                            <td style="font-size:12px;">{{ $mov->user->name ?? '—' }}</td>
                            <td class="td-mono">{{ $mov->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header"><span class="card-title">Stock Movements</span></div>
            <div style="text-align:center;color:var(--muted);padding:32px;font-size:13px;">
                No stock movements recorded yet.
            </div>
        </div>
        @endif
    </div>

    <!-- Right: image + stats -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        @if($product->image)
        <div class="card" style="padding:0;overflow:hidden;">
            <img src="{{ asset('storage/'.$product->image) }}"
                 alt="{{ $product->name_en }}"
                 style="width:100%;height:200px;object-fit:cover;">
        </div>
        @endif

        <!-- Pricing -->
        <div class="card">
            <div class="form-label">Selling Price</div>
            <div class="stat-value">${{ number_format($product->selling_price, 2) }}</div>
            <div class="form-text" style="margin-top:4px;">
                Buying: ${{ number_format($product->buying_price, 2) }}
            </div>
            @php
                $margin = $product->selling_price > 0
                    ? round(($product->selling_price - $product->buying_price) / $product->selling_price * 100, 1)
                    : 0;
            @endphp
            <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-size:12px;">
                <span class="text-muted">Margin</span>
                <span class="mono" style="color:{{ $margin >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                    ${{ number_format($product->selling_price - $product->buying_price, 2) }}
                    ({{ $margin }}%)
                </span>
            </div>
        </div>

        <!-- Stock -->
        <div class="card">
            <div class="form-label">Stock Level</div>
            <div class="stat-value {{ $product->isLowStock() ? 'text-danger' : 'text-success' }}">
                {{ $product->stock_quantity }}
            </div>
            <div class="form-text">
                Alert threshold: {{ $product->alert_quantity }} units
                @if($product->isLowStock())
                    <br><span style="color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> Low stock</span>
                @endif
            </div>
        </div>

        <!-- Sales stats -->
        <div class="card">
            <div class="form-label">Sales History</div>
            <div style="font-size:20px;font-weight:700;font-family:var(--mono);margin:6px 0 4px;">
                {{ $product->saleItems()->sum('quantity') }}
            </div>
            <div class="form-text">
                units sold in {{ $product->saleItems()->count() }} transactions
            </div>
            <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border);font-size:12px;color:var(--muted);">
                Revenue: <span class="mono" style="color:var(--text);">
                    ${{ number_format($product->saleItems()->sum('subtotal'), 2) }}
                </span>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card">
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach([
                    ['Created', $product->created_at->format('M d, Y')],
                    ['Updated', $product->updated_at->format('M d, Y')],
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
@endsection