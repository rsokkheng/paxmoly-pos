@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb', 'System / Users / Edit')

@section('topbar-actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
@endsection

@section('content')
<div style="max-width:560px;">
<div class="card">
    <div class="card-header">
        <span class="card-title">Edit — {{ $user->name }}</span>
        @php $roleBadge = match($user->role) { 'admin' => 'badge-danger', 'manager' => 'badge-info', default => 'badge-muted' }; @endphp
        <span class="badge {{ $roleBadge }}">{{ $user->role_label }}</span>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Role <span style="color:var(--danger);">*</span></label>
            <select name="role" class="form-control" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier — POS, basic reports</option>
                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager — Products, purchases, reports</option>
                <option value="admin"   {{ old('role', $user->role) === 'admin'   ? 'selected' : '' }}>Admin — Full access</option>
            </select>
            @if($user->id === auth()->id())
                {{-- Keep current role when select is disabled --}}
                <input type="hidden" name="role" value="{{ $user->role }}">
                <div class="form-text">You cannot change your own role.</div>
            @endif
            @error('role')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Status</label>
            <div style="display:flex;align-items:center;gap:12px;margin-top:4px;">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;">
                    <input type="radio" name="is_active" value="1" {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <span style="color:var(--success);"><i class="fas fa-circle" style="font-size:8px;"></i> Active</span>
                </label>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;">
                    <input type="radio" name="is_active" value="0" {{ old('is_active', $user->is_active ? '1' : '0') === '0' ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <span style="color:var(--danger);"><i class="fas fa-circle" style="font-size:8px;"></i> Inactive</span>
                </label>
            </div>
            @if($user->id === auth()->id())
                <input type="hidden" name="is_active" value="1">
                <div class="form-text">You cannot deactivate your own account.</div>
            @endif
        </div>

        <div class="form-group">
            <label class="form-label">New Password <span style="color:var(--muted);font-weight:400;">(leave blank to keep current)</span></label>
            <input type="password" name="password" class="form-control" placeholder="••••••••">
            <div class="form-text">Minimum 8 characters.</div>
            @error('password')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:10px;margin-top:4px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection
