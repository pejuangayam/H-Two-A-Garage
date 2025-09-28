@extends('layout4')

@section('title', 'View Item')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header">View Item</h1>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary shadow-sm px-4">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="row g-0">
            {{-- Image --}}
            <div class="col-md-5">
                @if($carPart->image_path)
                    <img src="{{ asset('storage/' . $carPart->image_path) }}" 
                         class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                         alt="{{ $carPart->name }}">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-light h-100 rounded-start">
                        <i class="bi bi-card-image fs-1 text-muted"></i>
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="col-md-7">
                <div class="card-body p-4">
                    <h3 class="card-title fw-bold text-dark mb-3">{{ $carPart->name }}</h3>

                    <p class="card-text text-muted mb-4">{{ $carPart->description ?? 'No description available.' }}</p>

                    <div class="inventory-details bg-light p-3 rounded mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Quantity:</span>
                            <span class="fw-semibold {{ $carPart->in_stock ? 'text-success' : 'text-danger' }}">
                                {{ $carPart->quantity }}
                            </span>
                        </div>

                        @if($carPart->price)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Price:</span>
                            <span class="fw-semibold">RM {{ number_format($carPart->price, 2) }}</span>
                        </div>
                        @endif

                        @if($carPart->part_number)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Part:</span>
                            <span class="fw-semibold">{{ $carPart->part_number }}</span>
                        </div>
                        @endif

                        @if($carPart->category)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Category:</span>
                            <span class="badge bg-secondary">{{ $carPart->category }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('inventory.edit', $carPart) }}" class="btn btn-warning shadow-sm px-3">
                            <i class="bi bi-pencil"></i> Edit
                        </a>

                        <form action="{{ route('inventory.destroy', $carPart) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger shadow-sm px-3">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>

                        {{-- PDF Download Button (Replaced Download Image) --}}
                        <a href="{{ route('inventory.download-pdf', $carPart) }}" class="btn btn-primary shadow-sm px-3">
                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 25px;
        padding-bottom: 10px;
        display: inline-block;
        border-bottom: 3px solid #3498db;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .inventory-details {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .btn {
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>
@endsection