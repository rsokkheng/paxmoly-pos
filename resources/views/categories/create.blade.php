@extends('layouts.app')
@section('title', 'New Category')
@section('page-title', 'New Category')
@section('breadcrumb', '<a href="'.route('categories.index').'" style="color:var(--muted);text-decoration:none;">Categories</a> <span class="sep">/</span> Create')

@section('content')
<div style="max-width:560px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Category Details</span>
        </div>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Electronics" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" placeholder="auto-generated">
                <div class="form-text">Leave blank to auto-generate from name</div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" placeholder="Optional description…">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-2" style="margin-top:8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('[name=name]').addEventListener('input', function() {
    const slug = document.getElementById('slug');
    if (!slug.dataset.manual) {
        slug.value = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    }
});
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
@endpush
