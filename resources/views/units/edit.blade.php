@extends('layouts.app')
@section('title', 'Edit Unit')
@section('page-title', 'Edit Unit')
@section('breadcrumb', 'Units / Edit')

@section('content')
<div style="max-width:460px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Edit: {{ $unit->name }}</span></div>
        <form action="{{ route('units.update', $unit) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Unit Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $unit->name) }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">ShortName *</label>
                <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $unit->short_name) }}" maxlength="10" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Unit</button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
