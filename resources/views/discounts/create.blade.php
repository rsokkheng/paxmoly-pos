@extends('layouts.app')
@section('title', 'New Discount')
@section('page-title', 'New Discount')
@section('breadcrumb', 'Discounts / Create')

@section('content')
<div style="max-width:580px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Discount Details</span></div>
        <form action="{{ route('discounts.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Discount Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Summer Sale" required autofocus>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Coupon Code</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g. SAVE10" style="text-transform:uppercase;">
                    <div class="form-text">Leave blank for auto-apply</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-control" id="discountType" required>
                        <option value="percentage" {{ old('type')=='percentage'?'selected':'' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type')=='fixed'?'selected':'' }}>Fixed Amount ($)</option>
                    </select>
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label" id="valueLabel">Discount Value *</label>
                    <input type="number" name="value" step="0.01" min="0" class="form-control" value="{{ old('value') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Uses</label>
                    <input type="number" name="max_uses" min="1" class="form-control" value="{{ old('max_uses') }}" placeholder="Unlimited">
                </div>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Minimum Order ($)</label>
                    <input type="number" name="min_order" step="0.01" min="0" class="form-control" value="{{ old('min_order', 0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Discount</button>
                <a href="{{ route('discounts.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('discountType').addEventListener('change', function() {
    document.getElementById('valueLabel').textContent = this.value === 'percentage' ? 'Percentage (%)' : 'Amount ($)';
});
document.querySelector('[name=code]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endpush
