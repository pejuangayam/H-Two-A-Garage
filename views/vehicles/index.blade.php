@extends('layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header">Vehicles</h1>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm rounded-3" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="d-flex justify-content-center mb-4">
        <form class="form-inline w-75" method="GET" action="{{ url('/vehicles') }}">
            <div class="input-group shadow-sm">
                <input 
                    name="search" 
                    class="form-control border-0 fs-6 py-2" 
                    type="search" 
                    placeholder=" 🔍 Search by vehicle number | model" 
                    aria-label="Search"
                    value="{{ request('search') }}"
                >
                <button class="btn btn-primary px-4" type="submit">Search</button>
                @if(request('search'))
                    <a href="{{ url('/vehicles') }}" class="btn btn-outline-secondary px-3">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <a href='/vehicles/create' class="btn btn-success mb-3 shadow-sm px-4">+ Add Vehicle</a>

    {{-- Vehicles Table --}}
    <div class="card shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-6">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 20%">Vehicle ID</th>
                            <th style="width: 20%">Model</th>
                            <th style="width: 15%">Date</th>
                            <th style="width: 15%">Services Count</th>
                            <th style="width: 25%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $index => $vehicle)
                        <tr>
                            <td>{{ ($vehicles->currentPage() - 1) * $vehicles->perPage() + $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $vehicle->vehicle_id }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ $vehicle->created_at->format('Y-m-d') }}</td>
                            <td class="fw-bold">{{ $vehicle->service->count() }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('services.manage', $vehicle->id) }}" 
                                       class="btn btn-sm btn-primary me-2 shadow-sm">Manage</a>
                                    <a href="{{ route('vehicles.edit', $vehicle->id) }}" 
                                       class="btn btn-sm btn-warning me-2 shadow-sm">Edit</a>
                                    <form onsubmit="return confirm('Are you sure?')" 
                                          action="{{ route('vehicles.destroy', $vehicle->id) }}" 
                                          method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm shadow-sm" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Pagination Links --}}
    @if($vehicles->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <div class="pagination-wrapper">
            {{ $vehicles->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

{{-- Styles --}}
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

    .table thead th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 14px 12px;
        font-size: 1rem;
    }

    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background: #f5f9ff;
        transition: 0.2s ease-in-out;
    }

    .btn {
        border-radius: 8px;
        font-size: 0.9rem;
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
@endsection
