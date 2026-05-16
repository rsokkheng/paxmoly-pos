@extends('layouts.app')
@section('title', 'Taxes')
@section('page-title', 'Tax Rates')
@section('breadcrumb', 'Taxes')

@section('topbar-actions')
    <a href="{{ route('taxes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Tax</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Tax Rates</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Rate (%)</th><th>Type</th><th>Status</th><th style="width:120px;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($taxes as $tax)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $tax->name }}</td>
                    <td class="mono">{{ $tax->rate }}%</td>
                    <td><span class="badge badge-info">{{ ucfirst($tax->type ?? 'inclusive') }}</span></td>
                    <td><span class="badge {{ $tax->is_active ? 'badge-success' : 'badge-muted' }}">{{ $tax->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-pencil"></i></a>
                            <form action="{{ route('taxes.destroy', $tax) }}" method="POST" onsubmit="return confirm('Delete this tax?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px;">No tax rates configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $taxes->links() }}</div>
</div>
@endsection
