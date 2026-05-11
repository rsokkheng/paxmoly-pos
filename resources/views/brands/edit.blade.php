@extends('layouts.app')
@section('title', 'Edit Brand')
@section('page-title', 'Edit Brand')
@section('breadcrumb', 'Brands / Edit')

@section('content')
<div style="max-width:780px;">
    <form action="{{ route('brands.update', $brand) }}" method="POST">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-header"><span class="card-title">Brand Information</span></div>
            <div class="form-group">
                <label class="form-label">Brand Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Optional description…">{{ old('description', $brand->description) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', $brand->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $brand->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Brand</button>
            <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
