@extends('layout')

@section('content')
<div class="container">
    <h1 class="mb-4 page-title">Manage Services for {{ $vehicle->model }} ({{ $vehicle->vehicle_id }})</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Global Add Service --}}
    <div class="card mb-4 add-service-card">
        <div class="card-body">
            <h5 class="card-title section-title"><i class="bi bi-plus-circle me-2"></i>Add New Service</h5>
            <form action="{{ route('services.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                <div class="row g-3">
                    <div class="col-sm-2">
                        <label class="form-label small text-muted">Date</label>
                        <input type="date" name="service_date"
                               value="{{ old('service_date', now()->format('Y-m-d')) }}"
                               class="form-control" required>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small text-muted">Item Name</label>
                        <input type="text" name="items" class="form-control" placeholder="Enter item name" required>
                    </div>
                    <div class="col-sm-1">
                        <label class="form-label small text-muted">Qty</label>
                        <input type="number" name="quantity" class="form-control" placeholder="Qty" min="1" required>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small text-muted">Price per Item</label>
                        <input type="number" step="0.01" name="per_price" class="form-control" placeholder="0.00" min="0" required>
                    </div>
                    <div class="col-sm-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i> Add Service
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Group services by date --}}
    @php
        $grouped = $vehicle->service->sortBy('service_date')->groupBy('service_date');
    @endphp

    @forelse($grouped as $date => $items)
        <div class="card mb-4 service-group-card">
            <div class="card-header service-group-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 date-title">
                        <i class="bi bi-calendar-date me-2"></i>
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleAddForm('{{ $date }}')">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                        <a href="{{ route('services.downloadPdf', ['vehicle' => $vehicle->id, 'date' => $date]) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Collapsible Add Form for this date --}}
                <div id="addForm-{{ $date }}" class="add-item-form mb-3 p-3 rounded d-none">
                    <form action="{{ route('services.store') }}" method="POST" class="row g-3">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                        <input type="hidden" name="service_date" value="{{ $date }}">
                        <div class="col-sm-3">
                            <input type="text" name="items" class="form-control" placeholder="Item Name" required>
                        </div>
                        <div class="col-sm-2">
                            <input type="number" name="quantity" class="form-control" placeholder="Qty" min="1" required>
                        </div>
                        <div class="col-sm-2">
                            <input type="number" step="0.01" name="per_price" class="form-control" placeholder="Price" min="0" required>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus me-1"></i> Add
                            </button>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="toggleAddForm('{{ $date }}')">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table service-table">
                        <thead>
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Per Price</th>
                                <th>Total</th>
                                <th style="width:180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $svc)
                                <tr id="row-{{ $svc->id }}">
                                    <td class="ps-3">{{ $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $svc->items }}</td>
                                    <td>{{ $svc->quantity }}</td>
                                    <td>{{ number_format($svc->per_price, 2) }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($svc->total, 2) }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-edit" onclick="toggleEdit({{ $svc->id }})">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <form action="{{ route('services.destroy', $svc->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-delete"
                                                        onclick="return confirm('Delete this item?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Inline edit row --}}
                                <tr id="editRow-{{ $svc->id }}" class="d-none edit-row">
                                    <td colspan="6" class="p-3">
                                        <form action="{{ route('services.update', $svc->id) }}" method="POST" class="row g-3">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="service_date" value="{{ $date }}">
                                            <div class="col-md-3">
                                                <input type="text" name="items" value="{{ $svc->items }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="quantity" value="{{ $svc->quantity }}" class="form-control" min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" step="0.01" name="per_price" value="{{ $svc->per_price }}" class="form-control" min="0" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="bi bi-check-lg"></i> Save
                                                </button>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-secondary w-100" onclick="toggleEdit({{ $svc->id }})">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- One Labour Row per Date --}}
                            @php
                                $labour = $items->max('labour_total');
                                $grand  = $items->sum('total') + $labour;
                            @endphp
                            <tr class="total-row">
                                <td colspan="3" class="text-end fw-bold ps-3">Labour Cost</td>
                                <td colspan="2">
                                    <form action="{{ route('services.update', $items->first()->id) }}" method="POST" class="d-flex">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="service_date" value="{{ $date }}">
                                        <input type="number" step="0.01" name="labour_total" value="{{ $labour }}" class="form-control me-2" style="max-width:120px;">
                                        <button type="submit" class="btn btn-info">
                                            <i class="bi bi-arrow-repeat me-1"></i> Update
                                        </button>
                                    </form>
                                </td>
                                <td class="fw-bold total-amount">Total: {{ number_format($grand, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card empty-state">
            <div class="card-body text-center py-5">
                <i class="bi bi-tools display-5 text-muted mb-3"></i>
                <h5 class="text-muted">No services yet</h5>
                <p class="text-muted">Add your first service using the form above</p>
            </div>
        </div>
    @endforelse

    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary mt-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Vehicles
    </a>
</div>

<script>
    function toggleEdit(id) {
        document.getElementById('row-' + id).classList.toggle('d-none');
        document.getElementById('editRow-' + id).classList.toggle('d-none');
    }
    
    function toggleAddForm(date) {
        const form = document.getElementById('addForm-' + date);
        form.classList.toggle('d-none');
    }
</script>

<style>
    .page-title {
        color: #2c3e50;
        font-weight: 600;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .section-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .add-service-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .service-group-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    
    .service-group-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .service-group-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        padding: 15px 20px;
    }
    
    .date-title {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .add-item-form {
        background-color: #f8f9fa;
        border: 1px dashed #dee2e6;
    }
    
    .service-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .service-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 12px 15px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .service-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .service-table tr:last-child td {
        border-bottom: none;
    }
    
    .service-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .edit-row {
        background-color: #fff3cd !important;
    }
    
    .total-row {
        background-color: #e3f2fd;
        font-weight: 600;
    }
    
    .total-amount {
        color: #1976d2;
        font-size: 1.05em;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-edit {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .btn-edit:hover {
        background-color: #ffeaa7;
        transform: translateY(-1px);
    }
    
    .btn-delete {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .btn-delete:hover {
        background-color: #f5c6cb;
        transform: translateY(-1px);
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
    }
    
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        transition: border-color 0.2s ease;
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
    }
    
    .empty-state {
        border: 2px dashed #dee2e6;
        background-color: #f8f9fa;
    }
    
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
</style>
@endsection