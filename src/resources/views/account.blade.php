{{-- 
    Original PHP code (will be handled by controllers):
    session_start();
    // Retrieve the userID cookie. If not set, redirect the user to the login page. If it is set, save it as $userID
    if(!isset($_COOKIE["userID"])){header("Location: login.php");}
    else {$userID = $_COOKIE["userID"];}

    $servername = "localhost";
    $username = "hackberr_399";
    $password = "MarthaBerry!";
    $dbname = "hackberr_399";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

    if (!isset($_COOKIE['userID'])) {
        header("Location: account.php");
        exit();
    }

    // Dummy user data (Replace this with database query)
    $user = [
        'username' => 'JohnDoe',
        'full_name' => 'John Doe',
        'bio' => 'Stock enthusiast | Investor | Market analyst',
        'profile_picture' => 'profile-placeholder.png',
        'predictions' => [
            ['symbol' => 'AAPL', 'prediction' => 'Bullish', 'accuracy' => '85%'],
            ['symbol' => 'TSLA', 'prediction' => 'Bearish', 'accuracy' => '90%'],
        ]
    ];
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user['username'] }} - SoVest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <style>
        body { background-color: #2c2c2c; color: #d4d4d4; }
        .navbar { background-color: #1f1f1f; }
        .profile-header { text-align: center; padding: 20px; }
        .profile-picture { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #28a745; }
        .bio { font-size: 1.1em; color: #b0b0b0; }
        .predictions-list { margin-top: 20px; }
        .prediction-card { background: #1f1f1f; padding: 15px; border-radius: 10px; }
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
                    <li class="nav-item"><a class="nav-link" href="{{ url('account') }}">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('logout') }}">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container profile-header">
        <img src="{{ asset($user['profile_picture']) }}" class="profile-picture" alt="Profile Picture">
        <h2>{{ $user['full_name'] }}</h2>
        <p class="bio">@{{ $user['username'] }} | {{ $user['bio'] }}</p>
    </div>

    <div class="container predictions-list">
        <h3 class="text-center">Predictions</h3>
        <div class="row">
            @foreach ($user['predictions'] as $prediction)
                <div class="col-md-4">
                    <div class="prediction-card">
                        <h5>{{ $prediction['symbol'] }}</h5>
                        <p>Prediction: <strong>{{ $prediction['prediction'] }}</strong></p>
                        <p>Accuracy: <strong>{{ $prediction['accuracy'] }}</strong></p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>