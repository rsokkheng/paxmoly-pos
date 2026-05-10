@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('breadcrumb', 'System / Users')

@section('topbar-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New User
    </a>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <span class="card-title">All Users</span>
        <span class="badge badge-info">{{ $users->count() }} users</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:var(--accent);
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:13px;font-weight:700;color:#000;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:500;">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <div style="font-size:10px;color:var(--muted);font-family:var(--mono);">You</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="td-mono">{{ $user->email }}</td>
                    <td>
                        @php
                            $roleBadge = match($user->role) {
                                'admin'   => 'badge-danger',
                                'manager' => 'badge-info',
                                'cashier' => 'badge-muted',
                            };
                            $roleIcon = match($user->role) {
                                'admin'   => 'fa-shield-alt',
                                'manager' => 'fa-user-tie',
                                'cashier' => 'fa-cash-register',
                            };
                        @endphp
                        <span class="badge {{ $roleBadge }}">
                            <i class="fas {{ $roleIcon }}" style="margin-right:4px;"></i>
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px;margin-right:4px;"></i>Active</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-circle" style="font-size:7px;margin-right:4px;"></i>Inactive</span>
                        @endif
                    </td>
                    <td class="td-mono">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Permission Reference Card --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <span class="card-title">Role Permission Matrix</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Permission</th>
                    <th style="text-align:center;width:100px;">
                        <span class="badge badge-muted"><i class="fas fa-cash-register" style="margin-right:4px;"></i>Cashier</span>
                    </th>
                    <th style="text-align:center;width:100px;">
                        <span class="badge badge-info"><i class="fas fa-user-tie" style="margin-right:4px;"></i>Manager</span>
                    </th>
                    <th style="text-align:center;width:100px;">
                        <span class="badge badge-danger"><i class="fas fa-shield-alt" style="margin-right:4px;"></i>Admin</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                $matrix = [
                    ['Process Sales (POS)',         true,  true,  true],
                    ['Apply Discount at Checkout',  true,  true,  true],
                    ['Manage Customers',            true,  true,  true],
                    ['View Sales Reports',          true,  true,  true],
                    ['Cancel / Refund Sales',       false, true,  true],
                    ['View Profit Reports',         false, true,  true],
                    ['Manage Products',             false, true,  true],
                    ['Manage Purchases',            false, true,  true],
                    ['Manage Categories & Units',   false, true,  true],
                    ['Manage Discounts',            false, true,  true],
                    ['Manage Suppliers',            false, true,  true],
                    ['Stock Adjustments',           false, true,  true],
                    ['Manage Taxes',                false, false, true],
                    ['Manage Users',                false, false, true],
                ];
                @endphp
                @foreach($matrix as [$label, $cashier, $manager, $admin])
                <tr>
                    <td style="font-size:13px;">{{ $label }}</td>
                    @foreach([$cashier, $manager, $admin] as $allowed)
                    <td style="text-align:center;">
                        @if($allowed)
                            <i class="fas fa-check" style="color:var(--success);"></i>
                        @else
                            <i class="fas fa-times" style="color:var(--danger);opacity:.4;"></i>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
