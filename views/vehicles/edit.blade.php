@extends('layout')

@section('content')

<div class="container mt-4">
    <!-- Styled Page Title -->
    <h2 class="page-title">Edit Vehicle</h2>

    <form method="POST" action="{{ route('vehicles.update', $vehicle->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="created_at" class="form-label">Date</label>
            <input type="date" 
                   class="form-control @error('created_at') is-invalid @enderror" 
                   name="created_at" 
                   id="created_at"
                   value="{{ old('created_at', $vehicle->created_at) }}">
            @error('created_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Vehicle Number --}}
        <div class="mb-3">
            <label for="vehicle_id" class="form-label">Vehicle Number</label>
            <input type="text" 
                   class="form-control @error('vehicle_id') is-invalid @enderror" 
                   name="vehicle_id" 
                   id="vehicle_id"
                   value="{{ old('vehicle_id', $vehicle->vehicle_id) }}">
            @error('vehicle_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Model --}}
        <div class="mb-3">
            <label for="model" class="form-label">Model</label>
            <input type="text" 
                   class="form-control @error('model') is-invalid @enderror" 
                   name="model" 
                   id="model"
                   value="{{ old('model', $vehicle->model) }}">
            @error('model')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- NoPhone --}}
        <div class="mb-3">
            <label for="noPhone" class="form-label">Number Phone <span class="text-danger">*</span></label>
            <input type="text" 
                   class="form-control @error('model') is-invalid @enderror" 
                   name="noPhone" 
                   id="noPhone"
                   value="{{ old('noPhone', $vehicle->noPhone) }}">
            @error('noPhone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Name --}}
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   name="name" 
                   id="name"
                   value="{{ old('name', $vehicle->name) }}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Kilometer --}}
        <div class="mb-3">
            <label for="kilometer" class="form-label">Kilometer</label>
            <input type="number" 
                   class="form-control @error('kilometer') is-invalid @enderror" 
                   name="kilometer" 
                   id="kilometer"
                   step="0.1"
                   value="{{ old('kilometer', $vehicle->kilometer ? number_format($vehicle->kilometer, 1, '.', '') : '') }}">
            @error('kilometer')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Buttons Row --}}
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update
            </button>
        </div>
    </form>
</div>

{{-- Inline Styling --}}
<style>
    .page-title {
        font-weight: 600;
        color: #2c3e50;
        padding-bottom: 10px;
        margin-bottom: 20px;
        border-bottom: 2px solid #3498db;
    }
</style>

@endsection
