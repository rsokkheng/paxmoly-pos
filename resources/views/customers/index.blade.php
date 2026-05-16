@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')
@section('breadcrumb', 'Customers')

@section('topbar-actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Customer</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Customers</span>
        <div class="search-box" style="width:220px;">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Search customers…" id="searchInput">
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Total Spent</th><th>Orders</th><th style="width:140px;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $customer->name }}</td>
                    <td class="td-mono">{{ $customer->email ?? '—' }}</td>
                    <td class="td-mono">{{ $customer->phone ?? '—' }}</td>
                    <td class="mono">${{ number_format($customer->total_spent ?? 0, 2) }}</td>
                    <td><span class="badge badge-info">{{ $customer->orders_count ?? 0 }}</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-pencil"></i></a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $customers->withQueryString()->links() }}</div>
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
