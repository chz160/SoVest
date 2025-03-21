@extends('layouts.app')

@section('title', 'My Account')

@section('content')
<div class="row">
    <!-- Account Information Column -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="{{ $user['profile_picture'] ?? '/images/default-avatar.png' }}" class="rounded-circle img-thumbnail" alt="Profile Picture" style="width: 150px; height: 150px; object-fit: cover;">
                    <h4 class="mt-3">{{ $user['full_name'] }}</h4>
                    <p class="text-muted">{{ $user['bio'] ?? 'Stock enthusiast' }}</p>
                </div>
                
                <div class="account-stats">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h6>Predictions</h6>
                            <h4>{{ $userStats['total_predictions'] ?? 0 }}</h4>
                        </div>
                        <div class="col-6 mb-3">
                            <h6>Avg. Accuracy</h6>
                            <h4>{{ number_format($userStats['avg_accuracy'] ?? 0, 0) }}%</h4>
                        </div>
                        <div class="col-6">
                            <h6>Correct</h6>
                            <h4>{{ $userStats['correct_predictions'] ?? 0 }}</h4>
                        </div>
                        <div class="col-6">
                            <h6>Reputation</h6>
                            <h4>{{ $userStats['reputation_score'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Account Update Form Column -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Edit Account Information</h5>
            </div>
            <div class="card-body">
                <!-- Display errors if any -->
                @if (!empty($error))
                    <div class="alert alert-danger">
                        @if ($error === 'validation_failed')
                            Please check your information and try again.
                        @elseif ($error === 'user_not_found')
                            User account not found. Please log in again.
                        @elseif ($error === 'system_error')
                            A system error occurred. Please try again later.
                        @else
                            An error occurred. Please try again.
                        @endif
                    </div>
                @endif

                <!-- Display success message -->
                @if (!empty($success))
                    <div class="alert alert-success">
                        Your account information has been updated successfully.
                    </div>
                @endif

                <!-- Account Update Form -->
                <form method="post" action="/account/update">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" value="{{ $user['first_name'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" value="{{ $user['last_name'] ?? '' }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user['username'] ?? '' }}">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="major" class="form-label">Major/Field</label>
                            <input type="text" class="form-control" id="major" name="major" value="{{ $user['major'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year/Position</label>
                            <input type="text" class="form-control" id="year" name="year" value="{{ $user['year'] ?? '' }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scholarship" class="form-label">Scholarship/Background</label>
                        <input type="text" class="form-control" id="scholarship" name="scholarship" value="{{ $user['scholarship'] ?? '' }}">
                    </div>
                    
                    <hr class="my-4">
                    <h5>Change Password</h5>
                    <p class="text-muted mb-3">Leave blank if you don't want to change your password</p>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Recent Predictions Section -->
        @if (!empty($user['predictions']))
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Recent Predictions</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Stock</th>
                            <th>Prediction</th>
                            <th>Target</th>
                            <th>End Date</th>
                            <th>Accuracy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user['predictions'] as $prediction)
                        <tr class="{{ isset($prediction['raw_accuracy']) && $prediction['raw_accuracy'] >= 80 ? 'table-success' : '' }}">
                            <td>{{ $prediction['symbol'] }}</td>
                            <td>{{ $prediction['prediction'] }}</td>
                            <td>${{ number_format($prediction['target_price'], 2) }}</td>
                            <td>{{ date('M j, Y', strtotime($prediction['end_date'])) }}</td>
                            <td>
                                @if ($prediction['accuracy'] === 'Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                <span class="badge {{ $prediction['raw_accuracy'] >= 80 ? 'bg-success' : ($prediction['raw_accuracy'] >= 50 ? 'bg-primary' : 'bg-danger') }}">{{ $prediction['accuracy'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="/predictions" class="btn btn-outline-primary btn-sm">View All Predictions</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection