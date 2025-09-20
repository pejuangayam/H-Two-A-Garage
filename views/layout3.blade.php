<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('h2logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>@yield('title', 'Dashboard')</title>
    <style>
        body {
            display: flex;
            background-color: #fdfdfd;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-width: 240px;
            max-width: 240px;
            background: linear-gradient(180deg, #ffffff, #f6f9fc);
            border-right: 1px solid #e5e7eb;
            color: #212529;
            height: 100vh;
            position: fixed;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }
        .brand {
            text-align: center;
            padding: 20px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .brand img {
            height: 32px;
            margin-bottom: 8px;
        }
        .brand span {
            display: block;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            letter-spacing: .5px;
        }
        .sidebar a {
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            margin: 6px 12px;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .sidebar a:hover {
            background: #eef6ff;
            color: #0d6efd;
            box-shadow: 0 2px 6px rgba(13,110,253,0.15);
            transform: translateX(4px);
        }
        .sidebar a.active {
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(13,110,253,0.3);
        }
        .content {
            margin-left: 240px;
            padding: 30px;
            width: 100%;
            background: #fdfdfd;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <img src="{{ asset('h2avector.png') }}" alt="H2 Garage Logo">
            <span>H2 A Garage</span>
        </div>

        <a href="{{ route('dashboard.index') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}">
            📊 Dashboard
        </a>
        <a href="{{ route('vehicles.index') }}" class="{{ request()->is('vehicles*') ? 'active' : '' }}">
            🚗 Service
        </a>
        <a href="{{ route('sell.index') }}" class="{{ request()->is('sell*') ? 'active' : '' }}">
            💰 Sales
        </a>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
