@extends('layouts.app')
@section('title', 'Product Sets')
@section('page-title', 'Product Sets')
@section('breadcrumb', 'Products / Sets')

@section('topbar-actions')
    <a href="{{ route('product-sets.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Set
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:16px;padding:12px 16px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:var(--radius);color:var(--success);font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="card" style="margin-bottom:16px;">
    <form method="GET" class="flex gap-2 items-center flex-wrap" style="padding:14px;">
        <div class="form-group" style="margin:0;flex:1;min-width:180px;">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search name or code…">
        </div>
        <div class="form-group" style="margin:0;">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option>
                <option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        <a href="{{ route('product-sets.index') }}" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Product Sets</span>
        <span style="font-size:12px;color:var(--muted);">{{ $sets->total() }} sets</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Set Name</th>
                    <th>Code</th>
                    <th>Components</th>
                    <th>Set Price</th>
                    <th>Available Qty</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sets as $set)
                @php
                    $set->load(['items.product']);
                    $avail = $set->availableQty();
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($set->image)
                                <img src="{{ asset('storage/'.$set->image) }}"
                                     alt="{{ $set->name }}"
                                     style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);border:1px solid var(--border);flex-shrink:0;">
                            @else
                                <div style="width:44px;height:44px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface2);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--muted);">
                                    <i class="fas fa-layer-group" style="font-size:16px;color:var(--accent);opacity:.7;"></i>
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;">{{ $set->name }}</div>
                                @if($set->description)
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $set->description }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="td-mono">{{ $set->code }}</td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:3px;">
                            @foreach($set->items as $item)
                            <span style="font-size:11px;color:var(--muted);">
                                <span style="font-weight:600;color:var(--text);">{{ $item->quantity }}×</span>
                                {{ $item->product->name ?? '—' }}
                                <span style="opacity:.6;">({{ strtoupper($item->unit_type) }})</span>
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="mono" style="font-weight:600;">${{ number_format($set->selling_price, 2) }}</td>
                    <td>
                        <span class="badge {{ $avail <= 0 ? 'badge-danger' : ($avail <= 5 ? 'badge-warn' : 'badge-success') }}">
                            {{ $avail }} sets
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('product-sets.toggle', $set) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="badge {{ $set->is_active ? 'badge-success' : 'badge-secondary' }}"
                                    style="border:none;cursor:pointer;font-size:11px;">
                                {{ $set->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('product-sets.show', $set) }}" class="btn btn-secondary btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('product-sets.edit', $set) }}" class="btn btn-secondary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('product-sets.destroy', $set) }}" onsubmit="return confirm('Delete this set?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger);" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">
                    No product sets yet. <a href="{{ route('product-sets.create') }}">Create one</a>.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $sets->withQueryString()->links() }}</div>
</div>
@endsection
