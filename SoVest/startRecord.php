<?php
	// Retrieve the userID cookie. If not set, redirect the user to the login page. If it is set, save it as $userID
	if(!isset($_COOKIE["userID"])){header("Location: login.php");}
	else {$userID = $_COOKIE["userID"];}

	$servername = "localhost";
    $username = "hackberr_399";
    $password = "MarthaBerry!";
    $dbname = "hackberr_399";
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {die("Connection failed: " . mysqli_connect_error());}

	$newTime = $_POST['newTime'];
	$workTime = strtotime($newTime);

	$query = "INSERT INTO npedigoLog (userID, start) VALUES ('$userID', '$workTime')";
    $result = mysqli_query($conn, $query) or die ("Could not insert.");

?>

<!doctype html>

<html lang="en" data-bs-theme="auto">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>WRK</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">   

		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
		<link rel="manifest" href="images/site.webmanifest">		
  	</head>
  	<body>

		<div class="container py-3">
  			<header>
   				<div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
				   <a href="index.php" class="d-flex align-items-center link-body-emphasis text-decoration-none">
       					<span class="fs-4">WRK</span>
					</a>

      				<nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="home.php">Home</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="about.php">About WRK</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="logout.php">Log Out</a>
      				</nav>
    			</div>
 		 	</header>

			<main>


				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">
        				<div class="card mb-4 rounded-3 shadow-sm">
          					<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">
									Come back and press "Done Working" once you have finished your task.
								</h4>
          					</div>
          					<div class="card-body">
							 
						  <a href = "endWork.php">
							<button class="btn btn-info w-100 py-2">Done Working</button>
							</a>
							</div>

							
        				</div>
      				</div>
				</div>


			</main>


			<footer class="pt-4 my-md-5 pt-md-5 border-top">
    			<div class="row">
      				<div class="col-12 col-md">
        				<small class="d-block mb-3 text-body-secondary">Created by Nate Pedigo</small>
      				</div>
				</div>
			</footer>
 
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
	</body>
</html>