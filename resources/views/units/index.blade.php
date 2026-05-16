@extends('layouts.app')
@section('title', 'Units')
@section('page-title', 'Units of Measure')
@section('breadcrumb', 'Units')

@section('topbar-actions')
    <a href="{{ route('units.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Unit</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">All Units</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>ShortName</th><th>Products</th><th style="width:120px;">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                <tr>
                    <td class="td-mono">{{ $loop->iteration }}</td>
                    <td style="font-weight:500;">{{ $unit->name }}</td>
                    <td><span class="badge badge-warn">{{ $unit->short_name }}</span></td>
                    <td>{{ $unit->products_count ?? 0 }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('units.edit', $unit) }}" class="btn btn-secondary btn-icon btn-sm"><i class="fas fa-pencil"></i></a>
                            <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Delete this unit?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No units found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $units->links() }}</div>
</div>
@endsection
