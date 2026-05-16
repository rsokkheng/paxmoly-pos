@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')
@section('breadcrumb', 'Suppliers')

@section('topbar-actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Supplier</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Suppliers</span>
        <div class="search-box" style="width:220px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search suppliers…" id="searchInput">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Company</th><th>Email</th><th>Phone</th><th>Balance</th><th style="width:140px;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $supplier->name }}</td>
                    <td>{{ $supplier->company ?? '—' }}</td>
                    <td class="td-mono">{{ $supplier->email ?? '—' }}</td>
                    <td class="td-mono">{{ $supplier->phone ?? '—' }}</td>
                    <td class="mono {{ ($supplier->balance??0) < 0 ? 'text-danger' : '' }}">${{ number_format($supplier->balance ?? 0, 2) }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary btn-icon btn-sm" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary btn-icon btn-sm" title="Edit"><i class="fas fa-pencil"></i></a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete this supplier?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">No suppliers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $suppliers->withQueryString()->links() }}</div>
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
