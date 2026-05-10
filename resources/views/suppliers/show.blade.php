@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title', $supplier->name)
@section('breadcrumb', 'Suppliers / Detail')

@section('topbar-actions')
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary"><i class="fas fa-pencil"></i> Edit</a>
    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Delete this supplier?')" style="display:inline;">
        @csrf @method('DELETE')
        <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
    </form>
@endsection

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">
    <!-- Info card -->
    <div class="card">
        <div class="card-header"><span class="card-title">Supplier Info</span></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            @foreach([
                ['Name', $supplier->name],
                ['Company', $supplier->company ?? '—'],
                ['Email', $supplier->email ?? '—'],
                ['Phone', $supplier->phone ?? '—'],
                ['City', $supplier->city ?? '—'],
                ['Country', $supplier->country ?? '—'],
            ] as [$label, $val])
            <div>
                <div class="form-label">{{ $label }}</div>
                <div style="font-size:14px;color:var(--text);">{{ $val }}</div>
            </div>
            @endforeach
        </div>
        @if($supplier->address)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
            <div class="form-label">Address</div>
            <div style="font-size:14px;">{{ $supplier->address }}</div>
        </div>
        @endif
        @if($supplier->notes)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
            <div class="form-label">Notes</div>
            <div style="font-size:14px;color:var(--muted);">{{ $supplier->notes }}</div>
        </div>
        @endif
    </div>

    <!-- Stats -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="form-label">Total Purchases</div>
            <div class="stat-value">${{ number_format($supplier->total_purchases ?? 0, 2) }}</div>
        </div>
        <div class="card">
            <div class="form-label">Outstanding Balance</div>
            <div class="stat-value {{ ($supplier->balance??0) < 0 ? 'text-danger' : 'text-success' }}">
                ${{ number_format(abs($supplier->balance ?? 0), 2) }}
            </div>
            <div class="form-text">{{ ($supplier->balance??0) < 0 ? 'You owe this supplier' : 'No outstanding balance' }}</div>
        </div>
    </div>
</div>
@endsection
