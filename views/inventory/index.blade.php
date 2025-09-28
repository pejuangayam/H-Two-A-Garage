@extends('layout4')

@section('title', 'Inventory Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header">Inventory Management</h1>
    </div>

    {{-- Success Messages --}}
    @if(session()->has('success'))
        <div class="alert alert-success shadow-sm rounded-3" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger shadow-sm rounded-3" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- AJAX Alert --}}
    <div id="ajax-alert" class="alert d-none" role="alert"></div>

    {{-- Search Bar --}}
    <div class="d-flex justify-content-center mb-3">
        <form class="form-inline w-75" method="GET" action="{{ route('inventory.index') }}">
            <div class="input-group shadow-sm">
                <input 
                    name="search" 
                    class="form-control border-0 fs-6 py-2" 
                    type="search" 
                    placeholder="🔍 Search by name, part number, or description" 
                    aria-label="Search"
                    value="{{ request('search') }}"
                >
                <button class="btn btn-primary px-4" type="submit">Search</button>
                @if(request('search'))
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary px-3">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Add New Item Button --}}
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('inventory.create') }}" class="btn btn-success shadow-sm px-4">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
    </div>

    {{-- Inventory Cards --}}
    @if($carParts->isEmpty())
        <div class="alert alert-info text-center shadow-sm rounded-3">
            <i class="bi bi-info-circle-fill"></i> No inventory items found. 
            <a href="{{ route('inventory.create') }}" class="alert-link">Add your first item</a>.
        </div>
    @else
        <div class="row">
            @foreach($carParts as $carPart)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm rounded-3 h-100">
                        {{-- Image --}}
                        <div class="position-relative">
                            @if($carPart->image_path)
                                <img src="{{ asset('storage/' . $carPart->image_path) }}" 
                                    class="card-img-top" 
                                    alt="{{ $carPart->name }}"
                                    style="height: 200px; object-fit: cover;">
                            @else
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" 
                                    style="height: 200px;">
                                    <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            
                            {{-- Stock Status Badge --}}
                            <span class="position-absolute top-0 end-0 m-2">
                                <span class="badge stock-badge-{{ $carPart->id }} {{ $carPart->quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $carPart->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </span>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title fw-semibold text-dark">{{ $carPart->name }}</h5>
                            <p class="card-text text-muted small">
                                {{ Str::limit($carPart->description, 80) }}
                            </p>
                            
                            <div class="inventory-details">
                                {{-- Simple Quantity Control --}}
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Quantity:</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="changeQuantity({{ $carPart->id }}, -1)"
                                                id="minus-btn-{{ $carPart->id }}"
                                                {{ $carPart->quantity <= 0 ? 'disabled' : '' }}>
                                            -
                                        </button>
                                        
                                        <input type="number" 
                                               class="form-control form-control-sm text-center" 
                                               id="quantity-{{ $carPart->id }}"
                                               value="{{ $carPart->quantity }}" 
                                               min="0" 
                                               max="999999"
                                               style="max-width: 80px;"
                                               onchange="updateQuantity({{ $carPart->id }})">
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success"
                                                onclick="changeQuantity({{ $carPart->id }}, 1)">
                                            +
                                        </button>
                                    </div>
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
                        </div>

                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('inventory.show', $carPart) }}" 
                                   class="btn btn-sm btn-outline-primary shadow-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                
                                <a href="{{ route('inventory.edit', $carPart) }}" 
                                   class="btn btn-sm btn-outline-warning shadow-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                
                                <form action="{{ route('inventory.destroy', $carPart) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                            
                            @if($carPart->image_path)
                            <div class="text-center">
                                <a href="{{ route('inventory.download', $carPart) }}" 
                                   class="btn btn-sm btn-success shadow-sm w-100">
                                    <i class="bi bi-download"></i> Download Image
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($carParts->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <div class="pagination-wrapper">
                {{ $carParts->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    @endif
</div>

<style>
    .page-header {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 25px;
        padding-bottom: 10px;
        display: inline-block;
        border-bottom: 3px solid #3498db;
    }

    .inventory-details {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        margin: 12px 0;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    /* 🔹 Pagination Redesign */
    .pagination-wrapper .pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        border-radius: 30px;
        padding: 4px 6px;
    }

    .page-link {
        border: none !important;
        border-radius: 20px !important;
        padding: 0.55rem 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        background-color: #f0f2f5;
        color: #3498db;
        transition: all 0.2s ease-in-out;
    }

    .page-link:hover {
        background-color: #3498db;
        color: #fff;
    }

    .page-item.active .page-link {
        background-color: #3498db !important;
        color: #fff !important;
        font-weight: 600;
    }

    .page-item.disabled .page-link {
        background-color: #e9ecef !important;
        color: #adb5bd !important;
        pointer-events: none;
    }
</style>

{{-- Fixed JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded'); // Debug line

    // Get CSRF token - with multiple fallbacks
    let token = '';
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        token = metaTag.getAttribute('content');
        console.log('CSRF token found:', token); // Debug line
    } else {
        console.error('CSRF token meta tag not found!');
        showAlert('CSRF token not found. Please refresh the page.', 'danger');
        return;
    }

    // Make functions global
    window.showAlert = function(message, type) {
        const alert = document.getElementById('ajax-alert');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.classList.remove('d-none');
        
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 3000);
    };

    window.changeQuantity = function(carPartId, change) {
        console.log('changeQuantity called:', carPartId, change); // Debug line
        
        const input = document.getElementById(`quantity-${carPartId}`);
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(0, currentValue + change);
        
        input.value = newValue;
        updateQuantity(carPartId);
    };

    window.updateQuantity = function(carPartId) {
        console.log('updateQuantity called:', carPartId); // Debug line
        
        const input = document.getElementById(`quantity-${carPartId}`);
        const quantity = parseInt(input.value) || 0;
        
        // Validate
        if (quantity < 0) {
            input.value = 0;
            return;
        }

        console.log('Sending request...'); // Debug line
        
        fetch(`/inventory/${carPartId}/update-quantity`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug line
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug line
            
            if (data.success) {
                // Update stock badge
                const badge = document.querySelector(`.stock-badge-${carPartId}`);
                if (badge) {
                    if (data.in_stock) {
                        badge.className = `badge stock-badge-${carPartId} bg-success`;
                        badge.textContent = 'In Stock';
                    } else {
                        badge.className = `badge stock-badge-${carPartId} bg-danger`;
                        badge.textContent = 'Out of Stock';
                    }
                }
                
                // Update minus button state
                const minusBtn = document.getElementById(`minus-btn-${carPartId}`);
                if (minusBtn) {
                    minusBtn.disabled = data.quantity <= 0;
                }
                
                showAlert('Quantity updated successfully!', 'success');
            } else {
                showAlert('Error: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error); // Debug line
            showAlert('Failed to update quantity: ' + error.message, 'danger');
        });
    };
});
</script>

@endsection
