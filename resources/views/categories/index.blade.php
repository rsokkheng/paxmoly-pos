@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'Categories')
@section('breadcrumb', 'Categories')

@section('topbar-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Category
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Categories</span>
        <div class="search-box" style="width:220px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search categories…" id="searchInput">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Created</th>
                    <th style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $category->name }}</td>
                    <td class="td-mono">{{ $category->slug }}</td>
                    <td><span class="badge badge-info">{{ $category->products_count ?? 0 }}</span></td>
                    <td class="td-mono">{{ $category->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-icon btn-sm" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:40px;">
                    No categories found. <a href="{{ route('categories.create') }}" style="color:var(--accent);">Create one</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $categories->links() }}</div>
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
