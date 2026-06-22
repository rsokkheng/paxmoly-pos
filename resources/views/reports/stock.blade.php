@extends('layouts.app')
@section('title', 'Stock Report')
@section('page-title', 'Stock Report')
@section('breadcrumb', 'Reports / Stock')

@section('topbar-actions')
    <a href="{{ route('reports.export', ['report' => 'stock']) . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export Excel
    </a>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print</button>
@endsection

@section('content')

<!-- Filters -->
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('reports.stock') }}" class="flex gap-2 items-center flex-wrap">
        <div class="form-group" style="margin:0;flex:1;min-width:160px;">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <option value="">All Categories</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:160px;">
            <label class="form-label">Stock Status</label>
            <select name="stock_status" class="form-control">
                <option value="">All</option>
                <option value="low" {{ request('stock_status')=='low'?'selected':'' }}>Low Stock</option>
                <option value="out" {{ request('stock_status')=='out'?'selected':'' }}>Out of Stock</option>
                <option value="ok" {{ request('stock_status')=='ok'?'selected':'' }}>In Stock</option>
            </select>
        </div>
        <div style="padding-top:20px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('reports.stock') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(4,1fr);">
    <div class="stat-card">
        <div class="stat-label">Total Products</div>
        <div class="stat-value">{{ $totalProducts ?? 0 }}</div>
        <div class="stat-sub">active products</div>
    </div>
    <div class="stat-card" style="--accent:#ef4444;">
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value">{{ $outOfStock ?? 0 }}</div>
        <div class="stat-sub">need restocking</div>
    </div>
    <div class="stat-card" style="--accent:#f0b429;">
        <div class="stat-label">Low Stock</div>
        <div class="stat-value">{{ $lowStock ?? 0 }}</div>
        <div class="stat-sub">below minimum</div>
    </div>
    <div class="stat-card" style="--accent:#22c55e;">
        <div class="stat-label">Stock Value</div>
        <div class="stat-value">${{ number_format($totalValue ?? 0, 2) }}</div>
        <div class="stat-sub">at cost price</div>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Stock Levels</span>
        <div class="search-box" style="width:200px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search products…" id="searchInput">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Cost Price</th>
                    <th>Sell Price</th>
                    <th>In Stock</th>
                    <th>Min Stock</th>
                    <th>Stock Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $status = $product->stock_quantity <= 0 ? 'out' : ($product->stock_quantity <= ($product->alert_quantity ?? 5) ? 'low' : 'ok');
                @endphp
                <tr>
                    <td style="font-weight:500;">
                        {{ $product->name_en }}
                        @if($product->name_kh)
                            <div style="font-size:11px;color:var(--muted);">{{ $product->name_kh }}</div>
                        @endif
                    </td>
                    <td class="td-mono">{{ $product->code }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td>{{ $product->unit->short_name ?? '—' }}</td>
                    <td class="mono">${{ number_format($product->buying_price, 2) }}</td>
                    <td class="mono">${{ number_format($product->selling_price, 2) }}</td>
                    <td class="mono {{ $status === 'out' ? 'text-danger' : ($status === 'low' ? 'text-accent' : 'text-success') }}" style="font-weight:600;">
                        {{ $product->stock_quantity }}
                    </td>
                    <td class="mono">{{ $product->alert_quantity ?? 5 }}</td>
                    <td class="mono">${{ number_format($product->stock_quantity * $product->buying_price, 2) }}</td>
                    <td>
                        @if($status === 'out')
                            <span class="badge badge-danger">Out of Stock</span>
                        @elseif($status === 'low')
                            <span class="badge badge-warn">Low Stock</span>
                        @else
                            <span class="badge badge-success">In Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;color:var(--muted);padding:40px;">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(r => r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none');
});
</script>
@endpush

@push('styles')
<style>
@media print {
    .sidebar, .topbar, .card:first-child, .pagination, #searchInput { display: none !important; }
    .main { margin: 0; }
    .content { padding: 0; }
    body { background: #fff; color: #000; }
}
</style>
@endpush
