<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SoVest - Social Stock Predictions Platform">
    <title>@yield('title', $pageTitle ?? 'SoVest')</title>
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
    
    <!-- Page-specific CSS -->
    @if (isset($pageCss))
    <link href="{{ asset($pageCss) }}" rel="stylesheet">
    @endif

    <!-- Yield and stack for styles -->
    @yield('styles')
    @stack('styles')
</head>
<body>
    <div class="container py-3">
        <!-- Header partial -->
        @include('partials.header')
        
        <main>
            <!-- Search bar partial -->
            @include('partials.search-bar')
            
            <!-- Error and success messages -->
            @if (!empty($errors))
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if (!empty($success) && !empty($message))
                <div class="alert alert-success">
                    {{ $message }}
                </div>
            @endif
            
            <!-- Main content area with backward compatibility -->
            @hasSection('content')
                @yield('content')
            @else
                {!! $content ?? '' !!}
            @endif
        </main>

        <!-- Footer partial -->
        @include('partials.footer')
    </div>
    
    <!-- Bootstrap JavaScript -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Page-specific JavaScript -->
    @if (isset($pageJs))
    <script src="{{ asset($pageJs) }}"></script>
    @endif

    <!-- Yield and stack for scripts -->
    @yield('scripts')
    @stack('scripts')
</body>
</html>