@extends('layouts.app')
@section('title', 'Discounts')
@section('page-title', 'Discounts')
@section('breadcrumb', 'Discounts')

@section('topbar-actions')
    <a href="{{ route('discounts.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Discount</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Discounts</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Code</th><th>Type</th><th>Value</th><th>Usage</th><th>Expires</th><th>Status</th><th style="width:120px;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($discounts as $discount)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $discount->name }}</td>
                    <td><span class="badge badge-warn mono">{{ $discount->code ?? '—' }}</span></td>
                    <td>{{ ucfirst($discount->type) }}</td>
                    <td class="mono">{{ $discount->type === 'percentage' ? $discount->value.'%' : '$'.number_format($discount->value,2) }}</td>
                    <td class="td-mono">{{ $discount->used_count ?? 0 }} / {{ $discount->max_uses ?? '∞' }}</td>
                    <td class="td-mono">{{ $discount->expires_at ? $discount->expires_at->format('Y-m-d') : '—' }}</td>
                    <td><span class="badge {{ $discount->is_active ? 'badge-success' : 'badge-muted' }}">{{ $discount->is_active ? 'Active' : 'Off' }}</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('discounts.edit', $discount) }}" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-pencil"></i></a>
                            <form action="{{ route('discounts.destroy', $discount) }}" method="POST" onsubmit="return confirm('Delete this discount?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:40px;">No discounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $discounts->links() }}</div>
</div>
@endsection
