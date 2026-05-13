@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')
@section('breadcrumb', 'Products')

@section('topbar-actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Product
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Products</span>
        <div class="flex gap-2">
            <form method="GET" action="{{ route('products.index') }}" class="flex gap-2" id="filterForm">
                <div class="search-box" style="width:200px;">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control"
                           placeholder="Name, brand or code…"
                           value="{{ request('search') }}"
                           id="searchInput">
                </div>
                <select name="category_id" class="form-control" style="width:160px;"
                        onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);cursor:pointer;white-space:nowrap;">
                    <input type="checkbox" name="low_stock" value="1"
                           {{ request('low_stock') ? 'checked' : '' }}
                           onchange="this.form.submit()">
                    Low stock only
                </label>
            </form>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Code</th>
                    <th>Category</th>
                    <th>Buying</th>
                    <th>Selling</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width:170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="td-mono">{{ $products->firstItem() + $loop->index }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($product->image)
                                <img src="{{ asset('storage/'.$product->image) }}"
                                     style="width:32px;height:32px;border-radius:4px;object-fit:cover;border:1px solid var(--border);">
                            @else
                                <div style="width:32px;height:32px;border-radius:4px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:14px;">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:500;">{{ $product->name }}</div>
                                @if($product->brand || $product->brand_name)
                                    <div style="font-size:12px;color:var(--muted);">
                                        {{ $product->brand->name ?? $product->brand_name }}
                                    </div>
                                @endif
                                <div class="td-mono">{{ $product->unit->abbreviation ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-mono">{{ $product->code }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td class="mono">${{ number_format($product->buying_price, 2) }}</td>
                    <td class="mono">${{ number_format($product->selling_price, 2) }}</td>
                    <td>
                        <span class="badge {{ $product->isLowStock() ? 'badge-danger' : 'badge-success' }}">
                            {{ $product->stock_quantity }}
                        </span>
                        @if($product->isLowStock())
                            <span style="font-size:10px;color:var(--danger);margin-left:2px;">⚠</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-muted' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('products.show', $product) }}"
                               class="btn btn-secondary btn-icon btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}"
                               class="btn btn-secondary btn-icon btn-sm" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <form action="{{ route('products.clone', $product) }}" method="POST"
                                  onsubmit="return confirm('Clone {{ addslashes($product->name) }}?')">
                                @csrf
                                <button class="btn btn-secondary btn-icon btn-sm" title="Clone" style="color:var(--accent);">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                  onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--muted);padding:48px;">
                        <i class="fas fa-box-open" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3;"></i>
                        @if(request('search') || request('category_id') || request('low_stock'))
                            No products match your filters.
                            <a href="{{ route('products.index') }}" style="color:var(--accent);">Clear filters</a>
                        @else
                            No products yet.
                            <a href="{{ route('products.create') }}" style="color:var(--accent);">Add one</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 0;">
        <div style="font-size:12px;color:var(--muted);font-family:var(--mono);">
            {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}
            @if(request('search') || request('category_id') || request('low_stock'))
                <span style="color:var(--accent);">(filtered)</span>
            @endif
        </div>
        <div class="pagination">{{ $products->withQueryString()->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Debounce: submit 400ms after user stops typing
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 400);
});
</script>
@endpush