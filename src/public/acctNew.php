<!doctype html>
<html lang="en" data-bs-theme="auto">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>SoVest Account Creation</title>
		<link href="css/bootstrap.min.css" rel="stylesheet">  
		
		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-16x16.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
		<link rel="manifest" href="images/site.webmanifest">		
  	</head>
  	<body>

		<div class="container py-3">
  			<header>
   				<div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
      				<a href="index.php" class="d-flex align-items-center link-body-emphasis text-decoration-none">
       					<span class="fs-4">SoVest</span>
					</a>

      				<nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
					  <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="home.php">Home</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="about.php">About SoVest</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="index.php">Log In</a>
      				</nav>
    			</div>

    			<div class="pricing-header p-3 pb-md-4 mx-auto text-center">
      				<p class="fs-5 text-body-secondary">Create an Account Below</p>
    			</div>
 		 	</header>

			<main>


				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">
        				<div class="card mb-4 rounded-3 shadow-sm">
          					<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">New User Registration</h4>
          					</div>
          					<div class="card-body">

								<!-- CUSTOMIZE THIS SECTION WITH FORM INFO -->

								<form action="acctCheck.php" method="post">
									<div class="form-floating">
     						 		    <input type="email" class="form-control" id="newEmail" name="newEmail" required>
 						      	 		  <label for="newEmail">Email</label>
 								 </div>
   								 <br>

								<div class="form-floating">
    							      <input type="password" class="form-control" id="newPass" name="newPass" required>
    							      <label for="newPass">Password</label>
      							  </div>
   								<br>

								<div class="form-floating">
      								  <select class="form-control" id="newMajor" name="newMajor">
										<option value="animalScience">Animal Science</option>
									    <option value="business">Business</option>
										<option value="computerScience">Computer Science</option>
										<option value="creativeTechnologies">Creative Technologies</option>
      								    <option value="education">Education</option>
      								    <option value="economics">Economics</option>
      								    <option value="humanities">Humanities</option>
     								   </select>
    								  <label for="newMajor">Current Major</label>
 								   </div>
 								   <br>
		


								<div class="form-floating">
      								  <select class="form-control" id="newYear" name="newYear">
										<option value="freshman">Freshman</option>
									    <option value="sophomore">Sophomore</option>
										<option value="junior">Junior</option>
										<option value="senior">Senior</option>
     								   </select>
    								  <label for="newYear">Current Grade Level</label>
 								   </div>
 								   <br>

							
									<div class="form-floating">
      								  <select class="form-control" id="newScholarship" name="newScholarship">
										<option value="yes">Yes</option>
									    <option value="no">No</option>
     								   </select>
    								  <label for="newScholarship">Are you the recipient of a work-based scholarship?</label>
 								   </div>
 								   <br>

									<button class="btn btn-primary w-100 py-2" type="submit">Submit</button>
								</form>

								<!-- END FORM INFO -->

							</div>
        				</div>
      				</div>
				</div>


			</main>


			<footer class="pt-4 my-md-5 pt-md-5 border-top">
    			<div class="row">
      				<div class="col-12 col-md">
        				<small class="d-block mb-3 text-body-secondary">Created by Nate Pedigo, Nelson Hayslett</small>
      				</div>
				</div>
			</footer>
 
		</div>
		<script src="js/bootstrap.bundle.min.js"></script>
	</body>
</html>