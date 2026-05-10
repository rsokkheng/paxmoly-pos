@extends('layouts.app')
@section('title', 'New Unit')
@section('page-title', 'New Unit')
@section('breadcrumb', 'Units / Create')

@section('content')
<div style="max-width:460px;">
    <div class="card">
        <div class="card-header"><span class="card-title">Unit Details</span></div>
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Unit Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Kilogram" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">ShortName *</label>
                <input type="text" name="short_name" class="form-control" value="{{ old('short_name') }}" placeholder="e.g. kg" maxlength="10" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Unit</button>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
