@extends('layouts.app')
@section('title', 'New Brand')
@section('page-title', 'New Brand')
@section('breadcrumb', 'Brands / Create')

@section('content')
<div style="max-width:780px;">
    <form action="{{ route('brands.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header"><span class="card-title">Brand Information</span></div>
            <div class="form-group">
                <label class="form-label">Brand Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Optional description…">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2" style="margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Brand</button>
            <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
