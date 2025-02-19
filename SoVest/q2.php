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
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q2.php">Work by Major</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q3.php">Work By Scholarship</a>
      				</nav>
    			</div>

 		 	</header>

			<main>


				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">

					<div class="card mb-4 rounded-3 shadow-sm">
							<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">Does a students Major effect how much work they do?</h4>
          					</div>

          					<div class="card-body">
								  
								<?php

									$servername = "localhost";
									$username = "hackberr_399";
									$password = "MarthaBerry!";
									$dbname = "hackberr_399";
									$conn = mysqli_connect($servername, $username, $password, $dbname);
									if (!$conn) {die("Connection failed: " . mysqli_connect_error());}
									
									$totalCRTWork = 0;
									$totalCRTTime = 0;

									$totalHumWork = 0;
									$totalHumTime = 0;

									$totalEcoWork = 0;
									$totalEcoTime = 0;

									$totalComWork = 0;
									$totalComTime = 0;

									$totalAniWork = 0;
									$totalAniTime = 0;


									$query = "SELECT * FROM npedigoLog";
									$result = mysqli_query($conn, $query) or die ("Could not select.");
									while ($row = mysqli_fetch_array($result)){
									extract($row);

										$workTime = $stop - $start;

										$query2 = "SELECT * FROM npedigoUser WHERE id = '$userID' ";
										$result2 = mysqli_query($conn, $query2) or die ("Could not select.");
										while ($row2 = mysqli_fetch_array($result2)){
										extract($row2);
										if($major == "creativeTechnologies"){$totalCRTWork = $totalCRTWork + 1; $totalCRTTime = $totalCRTTime + $workTime;}
										if($major == "animalScience"){$totalAniWork = $totalAniWork + 1; $totalAniTime = $totalAniTime + $workTime;}
										if($major == "economics"){$totalEcoWork = $totalEcoWork + 1; $totalEcoTime = $totalEcoTime + $workTime;}
										if($major == "humanities"){$totalHumWork = $totalHumWork + 1; $totalHumTime = $totalHumTime + $workTime;}
										if($major == "computerScience"){$totalComWork = $totalComWork + 1; $totalComTime = $totalComTime + $workTime;}
										}
								}			
							
									$avgCRTWork = round(($totalCRTTime / $totalCRTWork) / 3600, 2);
									$avgAniWork = round(($totalAniTime / $totalAniWork) / 3600, 2);
									
									$avgHumWork = round(($totalHumTime / $totalHumWork) / 3600, 2);
									$avgComWork = round(($totalComTime / $totalComWork) / 3600, 2);

									?>

							  <canvas class="my-4 w-100" id="chart" width="900" height="380"></canvas>

								<?php 
								echo "
									On Average, Creative Technologies majors work about $avgCRTWork outside of class per day. <br>
									On Average, Animal Science majors work about $avgHumWork outside of class per day. <br>
									On Average, Humanities majors work about $avgHumWork outside of class per day. <br>
									On Average, Computer Science Technologies majors work about $avgComWork outside of class per day. <br> <br> <br>";

									if($avgCRTWork > $avgAniWork && $avgCRTWork > $avgHumWork && $avgCRTWork > $avgComWork){ echo "Of all the majors researched, Creative Technologies students work the most outside of class. <br>";}
									if($avgAniWork > $avgCRTWork && $avgAniWork > $avgHumWork && $avgAniWork > $avgComWork){ echo "Of all the majors researched, Animal Science students work the most outside of class. <br>";}
									if($avgHumWork > $avgAniWork && $avgHumWork > $avgCRTWork && $avgHumWork > $avgComWork){ echo "Of all the majors researched, Humanities students work the most outside of class. <br>";}
									if($avgComWork > $avgAniWork && $avgComWork > $avgHumWork && $avgComWork > $avgCRTWork){ echo "Of all the majors researched, Computer Science students work the most outside of class. <br>";}

									if($avgCRTWork < $avgAniWork && $avgCRTWork < $avgHumWork && $avgCRTWork < $avgComWork){ echo "Of all the majors researched, Creative Technologies students work the least outside of class. <br>";}
									if($avgAniWork < $avgCRTWork && $avgAniWork < $avgHumWork && $avgAniWork < $avgComWork){ echo "Of all the majors researched, Animal Science students work the least outside of class. <br>";}
									if($avgHumWork < $avgAniWork && $avgHumWork < $avgCRTWork && $avgHumWork < $avgComWork){ echo "Of all the majors researched, Humanities students work the least outside of class. <br>";}
									if($avgComWork < $avgAniWork && $avgComWork < $avgHumWork && $avgComWork < $avgCRTWork){ echo "Of all the majors researched, Computer Science students work the least outside of class. <br>";}
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


		<script src="js/Chart.min.js"></script>	
					<script>
						var config = {
							type: 'bar',
							data: {
								labels: ['Creative Technologies','Animal Science','Humanities','Computer Science',],
								datasets: [
							
									{
										label: 'Work',
										backgroundColor: '#0909db',
										borderColor: '#00000a',
										borderWidth: 2,
										data:[<?php echo "$avgCRTWork, $avgAniWork, $avgHumWork, $avgComWork,"; ?>],
									},
								]
							},
							options: {
								scales: {
									xAxes: [{stacked: false, display: true, scaleLabel: {display: true, labelString: 'Majors'}}],
									yAxes: [{stacked: false, display: true, scaleLabel: {display: true,labelString: 'Average Hours Worked per Day'}}]
								}	
							}
						};

    // Loads the Data into the Page
    window.onload = function() {
        var ctx = document.getElementById('chart').getContext('2d');
        window.myLine = new Chart(ctx, config);
    };
</script>

	</body>
</html>