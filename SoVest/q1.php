<!doctype html>


<html lang="en" data-bs-theme="auto">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>WRK Admin Page</title>
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
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="results.php">Results</a>
					 	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q1.php">Are Students Overloaded</a>
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q2.php">Work By Major</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q3.php">Work By Scholarship</a>
      				</nav>
    			</div>

 		 	</header>

			<main>


				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">

					<div class="card mb-4 rounded-3 shadow-sm">
							<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">Are Students at Berry overworked?</h4>
          					</div>

          					<div class="card-body">
								  
								<?php

									$servername = "localhost";
									$username = "hackberr_399";
									$password = "MarthaBerry!";
									$dbname = "hackberr_399";
									$conn = mysqli_connect($servername, $username, $password, $dbname);
									if (!$conn) {die("Connection failed: " . mysqli_connect_error());}
									
									$totalWork = 0;
									$totalTime = 0;

									$query = "SELECT * FROM npedigoLog";
									$result = mysqli_query($conn, $query) or die ("Could not select.");
									while ($row = mysqli_fetch_array($result)){
									extract($row);
									$totalWork = $totalWork + 1;
									$totalTime = $totalTime + ($stop - $start);
								}			
							
								$avgWork = round(($totalTime / $totalWork) / 3600, 2);
								echo "Students at Berry work an average of $avgWork hours per day. The maximum recommended amount of outside work is 2 hours per day.";
								$maxHours = (2 - $avgWork);
								?>

							  <canvas class="my-4 w-100" id="chart" width="900" height="380"></canvas>

							  <?php 
							 
							 if($avgWork < 2){echo "<p> On average, students at Berry are within the range of healthy amounts of extracurricular work, according to the Journal of College Teaching and Learning.</p>";}
							 else{echo "<p>On average, students at Berry are exceeding the amounts of extracurricular work reccomended by the Journal of College Teaching and Learning </P>";}

							  ?>


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


				<!--Sample Doughnut Chart - Paste AFTER <script src="js/bootstrap.bundle.min.js"></script>-->
					<script src="js/Chart.min.js"></script>
					<script>
						var config = {
							type: 'doughnut',
							data: {
							labels: ['Hours Worked','Max Recommended Hours',],
							datasets: [			  	
								{
									label: 'Hours Worked',
									backgroundColor: ["#11ad00", "#97de9f"],
									data:[<?php echo "$avgWork , $maxHours,"; ?>],
								}, 
							]
							},
						};

						// Loads the Data into the Page
						window.onload = function() {
							var ctx = document.getElementById('chart').getContext('2d');
							window.myLine = new Chart(ctx, config);
						};
					</script>	

	</body>
</html>