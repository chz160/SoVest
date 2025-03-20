<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $pageTitle ?? 'SoVest')</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">   

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
    
    @if (isset($pageCss))
    <link href="{{ asset($pageCss) }}" rel="stylesheet">
    @endif

    @yield('styles')
</head>
<body>
    <div class="container py-3">
        <header>
            <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center link-body-emphasis text-decoration-none">
                    <span class="fs-4">SoVest</span>
                </a>

                <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
                    @if (isset($user))
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/home">Home</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/account">My Account</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/logout">Logout</a>
                    @else
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/home">Home</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/about">About SoVest</a>
                    @endif
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

        <main>
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
            
            @hasSection('content')
                @yield('content')
            @else
                {!! $content ?? '' !!}
            @endif
        </main>

        <footer class="pt-4 my-md-5 pt-md-5 border-top">
            <div class="row">
                <div class="col-12 col-md">
                    <small class="d-block mb-3 text-body-secondary">Created by Nate Pedigo, Nelson Hayslett</small>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    @if (isset($pageJs))
    <script src="{{ asset($pageJs) }}"></script>
    @endif

    @yield('scripts')
</body>
</html>