@extends('layouts.app')
@section('title', 'Edit Discount')
@section('page-title', 'Edit Discount')
@section('breadcrumb', 'Discounts / Edit')

@section('content')
<div style="max-width:580px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Edit: {{ $discount->name }}</span></div>
        <form action="{{ route('discounts.update', $discount) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Discount Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $discount->name) }}" required autofocus>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Coupon Code</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $discount->code) }}" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-control" required>
                        <option value="percentage" {{ old('type',$discount->type)=='percentage'?'selected':'' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type',$discount->type)=='fixed'?'selected':'' }}>Fixed Amount ($)</option>
                    </select>
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Discount Value *</label>
                    <input type="number" name="value" step="0.01" min="0" class="form-control" value="{{ old('value', $discount->value) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Uses</label>
                    <input type="number" name="max_uses" min="1" class="form-control" value="{{ old('max_uses', $discount->max_uses) }}" placeholder="Unlimited">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Minimum Order ($)</label>
                    <input type="number" name="min_order" step="0.01" min="0" class="form-control" value="{{ old('min_order', $discount->min_order) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $discount->expires_at?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ $discount->is_active?'selected':'' }}>Active</option>
                    <option value="0" {{ !$discount->is_active?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Discount</button>
                <a href="{{ route('discounts.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
