<?php
session_start(); // This should be the FIRST line of the file, before any output

         
    // Retrieve the userID cookie. If not set, redirect the user to the login page. If it is set, save it as $userID
	if(!isset($_COOKIE["userID"])){header("Location: login.php");}
	else {$userID = $_COOKIE["userID"];}

	$servername = "localhost";
    $username = "hackberr_399";
    $email = 'nthayslett@gmail.com';
    $password = "MarthaBerry!";
    $dbname = "hackberr_399";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    $query = "SELECT * from npedigoUser WHERE email = '{$email}'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $userID = $row['id'];
    if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

?>

<!doctype html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoVest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
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
            <a class="navbar-brand" href="index.php">SoVest</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="trending.php">Trending</a></li>
                    <?php if ($userID): ?>
                        <li class="nav-item"><a class="nav-link" href="account.php">My Account</a></li>
                   <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="index.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container text-center mt-5">
        <h1>Welcome to SoVest</h1>
        <p>Analyze, Predict, and Improve Your Market Insights</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="search.php" class="btn btn-primary">Search Stocks</a> 
            <a href="trending.php" class="btn btn-warning">Trending Predictions</a>
            <a href="<?php echo isset($userID) ? 'account.php' : 'login.php'; ?>" class="btn btn-success">My Account</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>