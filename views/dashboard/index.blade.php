@extends('layout3')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header with Year Selector -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header">Dashboard Overview ({{ $year }})</h1>
        <div>
            <form method="GET" action="{{ route('dashboard.index') }}" class="d-flex">
                <select class="form-select me-2" name="year" onchange="this.form.submit()">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}{{ $y > date('Y') ? ' (Future)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if($isFutureYear)
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i> 
        You are viewing a future year. Data will appear here once available.
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card revenue-card h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h3 class="mb-1">RM {{ number_format($yearlyServiceRevenue, 2) }}</h3>
                    <p class="mb-0 text-white-50">Service Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card sales-profit-card h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h3 class="mb-1">RM {{ number_format($yearlySalesProfit, 2) }}</h3>
                    <p class="mb-0 text-white-50">Sales Profit</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card services-card h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $activeServices }}</h3>
                    <p class="mb-0 text-white-50">Services Completed</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card month-card h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-trophy fa-2x mb-2"></i>
                    <h3 class="mb-1">{{ $topPerformingMonth }}</h3>
                    <p class="mb-0 text-white-50">Top Month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Service Revenue Chart -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Service Revenue ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Overall Profit From Services</span>
                        <span class="revenue-highlight">RM {{ number_format($yearlyServiceRevenue, 2) }}</span>
                    </div>
                    {!! $servicesChart->container() !!}
                </div>
            </div>
        </div>

        <!-- Profit vs Sales Chart -->
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Profit vs Sales ({{ $year }})</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Overall Profit From Sales</span>
                        <span class="revenue-highlight">RM {{ number_format($yearlySalesProfit, 2) }}</span>
                    </div>
                    {!! $salesChart->container() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
{!! $salesChart->script() !!}
{!! $servicesChart->script() !!}

<style>
.sales-profit-card {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

/* Keep all other existing styles */
.page-header {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #3498db;
}

.dashboard-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.3s;
    border: none;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.stat-card {
    text-align: center;
    border-radius: 12px;
    color: white;
    border: none;
}

.stat-card .card-body {
    padding: 1.5rem;
}

.stat-card i {
    font-size: 2.5rem;
    margin-bottom: 15px;
    opacity: 0.9;
}

.stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.revenue-card {
    background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
}

.services-card {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.vehicles-card {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
}

.month-card {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.revenue-highlight {
    font-size: 1.4rem;
    font-weight: bold;
    color: #27ae60;
    background: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border-left: 4px solid #27ae60;
}

.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
    font-weight: 600;
}

.text-white-50 {
    opacity: 0.9;
}
</style>
@endsection