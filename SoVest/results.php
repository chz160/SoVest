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
       					<span class="fs-4">WRK Results</span>
					</a>

      				<nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="results.php">Results</a>
					 	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q1.php">Are Students Overloaded</a>
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q2.php">Work By Major</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="q3.php">Work by Scholarship</a>
      				</nav>
    			</div>

 		 	</header>

			<main>


				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">

					<div class="card mb-4 rounded-3 shadow-sm">
							<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">WRK Results and Findings</h4>
          					</div>

          					<div class="card-body">
							  <a href="q1.php"><button type="button" class="btn btn-primary">Are Students at Berry overworked?</button></a><br><br>
							  <a href="q2.php"><button type="button" class="btn btn-info">Does a Students Major affect how much work they do?</button></a><br><br>
							  <a href="q3.php"><button type="button" class="btn btn-secondary">Does having a scholarship to Berry result in students being overworked?</button></a>
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