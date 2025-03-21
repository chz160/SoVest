{{-- 
    SoVest - Standardized Header
    
    This file contains the standard header used across all pages in the application.
    It includes the navigation menu, search functionality, and common meta tags.
--}}

<header>
    <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
        <a href="{{ route('home') }}" class="d-flex align-items-center link-body-emphasis text-decoration-none">
            <img src="{{ asset('images/logo.png') }}" width="50px" alt="SoVest Logo" class="me-2">
            <span class="fs-4">SoVest</span>
        </a>

        <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
            <ul class="navbar-nav d-flex flex-row">
                <li class="nav-item me-3">
                    <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('home') ? 'active' : '' }}" 
                       href="{{ route('user.home') }}">Home</a>
                </li>
                <li class="nav-item me-3">
                    <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('search') ? 'active' : '' }}" 
                       href="{{ route('search') }}">Search</a>
                </li>
                <li class="nav-item me-3">
                    <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('predictions/trending') ? 'active' : '' }}" 
                       href="{{ route('predictions.trending') }}">Trending</a>
                </li>
                
                @auth
                    <li class="nav-item me-3">
                        <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('predictions') ? 'active' : '' }}" 
                           href="{{ route('predictions.index') }}">My Predictions</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('account') ? 'active' : '' }}" 
                           href="{{ route('user.account') }}">My Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="py-2 link-body-emphasis text-decoration-none" 
                           href="{{ route('logout') }}">Logout</a>
                    </li>
                @else
                    <li class="nav-item me-3">
                        <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('login') ? 'active' : '' }}" 
                           href="{{ route('login.form') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="py-2 link-body-emphasis text-decoration-none {{ request()->is('register') ? 'active' : '' }}" 
                           href="{{ route('register.form') }}">Sign Up</a>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
    
    @if (!empty($pageHeader))
    <div class="pricing-header p-3 pb-md-4 mx-auto text-center">
        <h1 class="display-4 fw-normal">{{ $pageHeader }}</h1>
        @if (!empty($pageSubheader))
        <p class="fs-5 text-body-secondary">{{ $pageSubheader }}</p>
        @endif
    </div>
    @endif
</header>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .navbar-nav {
            list-style: none;
            padding-left: 0;
        }
        .navbar-nav .nav-item {
            display: inline-block;
        }
        .active {
            font-weight: bold;
        }
    </style>
@endpush