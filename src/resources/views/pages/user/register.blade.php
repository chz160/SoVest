@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <!-- Display errors if any -->
                @if (!empty($error))
                    <div class="alert alert-danger">
                        @if ($error === 'invalid_email')
                            Please enter a valid email address.
                        @elseif ($error === 'password_too_short')
                            Password must be at least 6 characters long.
                        @elseif ($error === 'validation_failed')
                            Please check your information and try again.
                        @elseif ($error === 'system_error')
                            A system error occurred. Please try again later.
                        @else
                            An error occurred. Please try again.
                        @endif
                    </div>
                @endif

                <!-- Registration Form -->
                <form method="post" action="{{ route('register.submit') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="newEmail" name="newEmail" required>
                        <div class="form-text">We'll never share your email with anyone else.</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="newPass" class="form-label">Password</label>
                            <input type="password" class="form-control" id="newPass" name="newPass" required>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmPass" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPass" name="confirmPass" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="newMajor" class="form-label">Major/Field (Optional)</label>
                            <input type="text" class="form-control" id="newMajor" name="newMajor">
                        </div>
                        <div class="col-md-6">
                            <label for="newYear" class="form-label">Year/Position (Optional)</label>
                            <input type="text" class="form-control" id="newYear" name="newYear">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newScholarship" class="form-label">Scholarship/Background (Optional)</label>
                        <input type="text" class="form-control" id="newScholarship" name="newScholarship">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="termsAgreement" name="termsAgreement" required>
                        <label class="form-check-label" for="termsAgreement">I agree to the <a href="#">Terms and Conditions</a></label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="/login">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection