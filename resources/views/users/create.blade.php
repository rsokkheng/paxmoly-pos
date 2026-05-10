@extends('layouts.app')
@section('title', 'New User')
@section('page-title', 'New User')
@section('breadcrumb', 'System / Users / Create')

@section('topbar-actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
@endsection

@section('content')
<div style="max-width:560px;">
<div class="card">
    <div class="card-header"><span class="card-title">Create User Account</span></div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            @error('name')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Role <span style="color:var(--danger);">*</span></label>
            <select name="role" class="form-control" required>
                <option value="">— Select role —</option>
                <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier — POS, basic reports</option>
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager — Products, purchases, reports</option>
                <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin — Full access</option>
            </select>
            @error('role')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Password <span style="color:var(--danger);">*</span></label>
            <input type="password" name="password" class="form-control" required>
            <div class="form-text">Minimum 8 characters.</div>
            @error('password')<div class="form-text" style="color:var(--danger);">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:10px;margin-top:4px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Create User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection
