{{-- Original file included CSRF protection with require_once '/crsf.php' --}}
<!doctype html>
<html lang="en" data-bs-theme="auto">
	<head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<title>SoVest</title>
		<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">   

		<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-16x16.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
		<link rel="manifest" href="{{ asset('images/site.webmanifest') }}">	
		<style>
			.card-body{
				width: 70%;
				padding-left: 30%;
			}
		</style>
  	</head>
  	<body>

		<div class="container py-3">
  			<header>
   				<div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
				   <a href="{{ url('/') }}" class="d-flex align-items-center link-body-emphasis text-decoration-none">
       					<span class="fs-4">SoVest</span>
					</a>

      				<nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
					  	<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="{{ url('/home') }}">Home</a>
	  					<a class="me-3 py-2 link-body-emphasis text-decoration-none" href="{{ url('/about') }}">About SoVest</a>
      				</nav>
    			</div>

    			<div class="pricing-header p-3 pb-md-4 mx-auto text-center">
      				<p class="fs-5 text-body-secondary">SoVest aims to bring stock tips to the people, through a fun and innovative platform.</p>
    			</div>
 		 	</header>

			<main>

				<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">

					<div class="col">
        				<div class="card mb-4 rounded-3 shadow-sm">
          					<div class="card-header py-3">
            					<h4 class="my-0 fw-normal">SoVest</h4>
          					</div>
          					<div class="card-body">
							  	<p>Sign up now to access stock picks from talented individuals and make your own predictions to boost that REP score!</p>
								  <form action="{{ url('/login/submit') }}" method="post">
								  @csrf

									<div class="form-floating">
									<input type="email" class="form-control" id="email" name="email" required>
									<label for="email">Email</label>
										</div>
											<br>
											
									<!-- SAMPLE PASSWORD FORM (WITH REQUIRED) -->
										<div class="form-floating">
									<input type="password" class="form-control" id="password" name="password" required>
									<label for="password">Password</label>
										</div>
										<br>
									

								<a href="{{ url('/home') }}">
									<button class="btn btn-success w-100 py-2" type="submit">Log In</button>
								</a>
									</form>
								<br>
								<br>
								<p>New to SoVest? <a href="{{ url('/acctNew') }}">Sign Up Here!</a></p>

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
		<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
	</body>
</html>