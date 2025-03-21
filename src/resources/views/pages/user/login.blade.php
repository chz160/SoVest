@extends('layouts.app')

@section('title', $pageTitle ?? 'Login')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <!-- Display errors if any -->
                @if (!empty($error))
                    <div class="alert alert-danger">
                        @if ($error === 'invalid_credentials')
                            Invalid email or password. Please try again.
                        @elseif ($error === 'system_error')
                            A system error occurred. Please try again later.
                        @else
                            An error occurred. Please try again.
                        @endif
                    </div>
                @endif

                <!-- Display success message if registration was successful -->
                @if (!empty($success))
                    <div class="alert alert-success">
                        Account created successfully! You can now log in.
                    </div>
                @endif

                <!-- Login Form -->
                <form method="post" action="/login/submit">
                    <div class="mb-3">
                        <label for="tryEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="tryEmail" name="tryEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tryPass" class="form-label">Password</label>
                        <input type="password" class="form-control" id="tryPass" name="tryPass" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe" value="1">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="/register">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection