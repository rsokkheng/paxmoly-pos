@extends('layouts.app')
@section('title', 'Brands')
@section('page-title', 'Brands')
@section('breadcrumb', 'Brands')

@section('topbar-actions')
    <a href="{{ route('brands.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Brand
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Brands</span>
        <div class="search-box" style="width:220px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search brands…" id="searchInput">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Products</th>
                    <th>Created</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brands as $brand)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $brand->name }}</td>
                    <td><span class="badge badge-info">{{ $brand->products_count ?? 0 }}</span></td>
                    <td class="td-mono">{{ $brand->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('brands.edit', $brand) }}" class="btn btn-secondary btn-icon btn-sm" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <form action="{{ route('brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Delete this brand?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--muted); padding:40px;">
                    No brands found. <a href="{{ route('brands.create') }}" style="color:var(--accent);">Create one</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $brands->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
