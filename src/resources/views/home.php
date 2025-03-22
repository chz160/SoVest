<!doctype html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoVest</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <style>
        body {
            background-color: #2c2c2c;
            color: #d4d4d4;
        }
        .navbar {
            background-color: #1f1f1f;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">SoVest</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('search') }}">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('trending') }}">Trending</a></li>
                    @if (Auth::check())
                        <li class="nav-item"><a class="nav-link" href="{{ url('account') }}">My Account</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Login</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-center mt-5">
        <h1>Welcome to SoVest</h1>
        <p>Analyze, Predict, and Improve Your Market Insights</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="{{ url('search') }}" class="btn btn-primary">Search Stocks</a> 
            <a href="{{ url('trending') }}" class="btn btn-warning">Trending Predictions</a>
            <a href="{{ Auth::check() ? url('account') : url('login') }}" class="btn btn-success">My Account</a>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>