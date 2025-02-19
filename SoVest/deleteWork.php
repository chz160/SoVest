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
									Edit work logs here to correct any errors.
								</h4>
          					</div>
          					<div class="card-body">


							  <table class="table table-striped">																			
								<tr>																		<!-- The <tr> tag creates a new row of data -->
									<th  scope="col">Start</th>														<!-- The <th> tag creates header item in the table -->
									<th  scope="col">End</th>
									<th  scope="col">Work Involvement</th>
									<th  scope="col">Delete Log

									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-backspace-fill" viewBox="0 0 16 16">
									<path d="M15.683 3a2 2 0 0 0-2-2h-7.08a2 2 0 0 0-1.519.698L.241 7.35a1 1 0 0 0 0 1.302l4.843 5.65A2 2 0 0 0 6.603 15h7.08a2 2 0 0 0 2-2zM5.829 5.854a.5.5 0 1 1 .707-.708l2.147 2.147 2.146-2.147a.5.5 0 1 1 .707.708L9.39 8l2.146 2.146a.5.5 0 0 1-.707.708L8.683 8.707l-2.147 2.147a.5.5 0 0 1-.707-.708L7.976 8z"/>
									</svg>

									</th>
								</tr>																		<!-- The </tr> tag ends the row of the table -->

								<?php
									$query = "SELECT * FROM npedigoLog WHERE userID = '$userID' ORDER BY id DESC";
									$result = mysqli_query($conn, $query) or die ("Could not select.");
									while ($row = mysqli_fetch_array($result)){
										extract($row);
										$startNice = date("d M g:i A",$start);
										$stopNice = date("d M g:i A",$stop);
										$involvementNice = "";
										if($involvement == 0){$involvementNice = "Low Involvement";}
										if($involvement == 1){$involvementNice = "Moderate Involvement";}
										if($involvement == 2){$involvementNice = "High Involvement";}
										if($involvement == 3){$involvementNice = "Full Involvement";}
										echo"		
												<tr>																		
													<td>$startNice</td>
													<td>$stopNice</td>	
													<td>$involvementNice</td>		
													<td>
														<a href=\"deleteRecord.php?id=$id\">
														<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-backspace-fill\" viewBox=\"0 0 16 16\">
														<path d=\"M15.683 3a2 2 0 0 0-2-2h-7.08a2 2 0 0 0-1.519.698L.241 7.35a1 1 0 0 0 0 1.302l4.843 5.65A2 2 0 0 0 6.603 15h7.08a2 2 0 0 0 2-2zM5.829 5.854a.5.5 0 1 1 .707-.708l2.147 2.147 2.146-2.147a.5.5 0 1 1 .707.708L9.39 8l2.146 2.146a.5.5 0 0 1-.707.708L8.683 8.707l-2.147 2.147a.5.5 0 0 1-.707-.708L7.976 8z\"/>
														</svg>
															</a>
													</td>
												</tr>
												";
									}	
								?>


							</table>

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