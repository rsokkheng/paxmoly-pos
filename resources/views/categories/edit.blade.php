@extends('layouts.app')
@section('title', 'Edit Category')
@section('page-title', 'Edit Category')
@section('breadcrumb', '<a href="'.route('categories.index').'" style="color:var(--muted);text-decoration:none;">Categories</a> <span class="sep">/</span> Edit')

@section('content')
<div style="max-width:560px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Edit: {{ $category->name }}</span>
        </div>
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="flex gap-2" style="margin-top:8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Category</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
