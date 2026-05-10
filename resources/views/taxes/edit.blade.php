@extends('layouts.app')
@section('title', 'Edit Tax')
@section('page-title', 'Edit Tax Rate')
@section('breadcrumb', 'Taxes / Edit')

@section('content')
<div style="max-width:500px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Edit: {{ $tax->name }}</span></div>
        <form action="{{ route('taxes.update', $tax) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Tax Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $tax->name) }}" required autofocus>
            </div>
            <div class="form-row cols-2">
                <div class="form-group">
                    <label class="form-label">Rate (%) *</label>
                    <input type="number" name="rate" step="0.01" min="0" max="100" class="form-control" value="{{ old('rate', $tax->rate) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="inclusive" {{ old('type',$tax->type)=='inclusive'?'selected':'' }}>Inclusive</option>
                        <option value="exclusive" {{ old('type',$tax->type)=='exclusive'?'selected':'' }}>Exclusive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" {{ $tax->is_active?'selected':'' }}>Active</option>
                    <option value="0" {{ !$tax->is_active?'selected':'' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Tax</button>
                <a href="{{ route('taxes.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
