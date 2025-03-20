@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('styles')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<style>
    body { background-color: #2c2c2c; color: #d4d4d4; }
    .navbar { background-color: #1f1f1f; }
    .card { background-color: #1f1f1f; border: none; margin-bottom: 20px; }
    .stat-card { text-align: center; padding: 20px; }
    .stat-value { font-size: 2.5rem; font-weight: bold; }
    .stat-label { color: #b0b0b0; }
    .system-info { list-style: none; padding: 0; }
    .system-info li { padding: 8px 0; border-bottom: 1px solid #3c3c3c; }
    .warning-list { background-color: #332701; border-left: 4px solid #f0ad4e; }
    .error-log { background-color: #2d0000; max-height: 300px; overflow-y: auto; }
</style>
@endsection

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">SoVest Admin</a>
        <img src="{{ asset('images/logo.png') }}" width="50px" alt="SoVest Logo">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.stocks.manage') }}">Manage Stocks</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Main Site</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('logout') }}">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>System Status Dashboard</h1>
    
    <div class="row">
        <div class="col-md-6">
            <h2 class="mt-4">Quick Actions</h2>
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.stocks.update') }}" class="btn btn-success">Update Stock Prices</a>
                        <a href="{{ route('admin.predictions.evaluate') }}" class="btn btn-warning">Evaluate Predictions</a>
                        <a href="{{ route('admin.maintenance') }}" class="btn btn-info">Run Database Maintenance</a>
                        <a href="{{ route('admin.logs.clear') }}" class="btn btn-danger">Clear Error Logs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2 class="mt-4">System Status</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table table-dark">
                        <tr>
                            <td>Stock API Status:</td>
                            <td><span class="badge bg-{{ $apiStatusClass ?? 'success' }}">{{ $apiStatus ?? 'OK' }}</span></td>
                        </tr>
                        <tr>
                            <td>Database Status:</td>
                            <td><span class="badge bg-success">Connected</span></td>
                        </tr>
                        <tr>
                            <td>Last Stock Update:</td>
                            <td>{{ $stats['last_stock_update'] ?? 'Never' }}</td>
                        </tr>
                        <tr>
                            <td>Last Price Update:</td>
                            <td>{{ $stats['last_price_update'] ?? 'Never' }}</td>
                        </tr>
                        <tr>
                            <td>Database Size:</td>
                            <td>{{ $stats['database_size'] ?? 0 }} MB</td>
                        </tr>
                        <tr>
                            <td>Log Files Size:</td>
                            <td>{{ $stats['log_size'] ?? 0 }} MB</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <h2 class="mt-4">Application Statistics</h2>
    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value text-info">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value text-success">{{ $stats['active_users'] ?? 0 }}</div>
                <div class="stat-label">Active Users (30d)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value text-warning">{{ $stats['total_predictions'] ?? 0 }}</div>
                <div class="stat-label">Total Predictions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="stat-value text-primary">{{ $stats['total_stocks'] ?? 0 }}</div>
                <div class="stat-label">Tracked Stocks</div>
            </div>
        </div>
    </div>
    
    @if (!empty($warnings))
    <div class="card mt-4 warning-list">
        <div class="card-body">
            <h4><i class="bi bi-exclamation-triangle-fill text-warning"></i> System Warnings</h4>
            <ul>
                @foreach ($warnings as $warning)
                <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h2>System Information</h2>
            <div class="card">
                <div class="card-body">
                    <ul class="system-info">
                        <li><strong>PHP Version:</strong> {{ $systemInfo['php_version'] ?? '' }}</li>
                        <li><strong>Web Server:</strong> {{ $systemInfo['server_software'] ?? '' }}</li>
                        <li><strong>MySQL Version:</strong> {{ $systemInfo['mysql_version'] ?? '' }}</li>
                        <li><strong>Operating System:</strong> {{ $systemInfo['operating_system'] ?? '' }}</li>
                        <li><strong>Memory Limit:</strong> {{ $systemInfo['memory_limit'] ?? '' }}</li>
                        <li><strong>Max Execution Time:</strong> {{ $systemInfo['max_execution_time'] ?? 0 }}s</li>
                        <li><strong>Upload Max Filesize:</strong> {{ $systemInfo['upload_max_filesize'] ?? '' }}</li>
                        <li><strong>Post Max Size:</strong> {{ $systemInfo['post_max_size'] ?? '' }}</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2>Recent Error Logs</h2>
            <div class="card">
                <div class="card-body error-log">
                    @if (empty($errors))
                        <p class="text-success">No errors in log file.</p>
                    @else
                        <pre>{{ implode("\n", $errors) }}</pre>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection