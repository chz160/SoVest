<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>About SoVest</title>
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-16x16') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
	</head>
	<body>
<div class="container py-3">
  <header>
   <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
   <a href="{{ url('index') }}" class="d-flex align-items-center link-body-emphasis text-decoration-none">
       <span class="fs-4">SoVest</span>
</a>
      <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
  <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="{{ url('home') }}">Home</a>
                                                <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="{{ url('about') }}">About SoVest</a>
                                                <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="{{ url('login') }}">Log In</a>
                                </nav>
                        </div>
                        <div class="pricing-header p-3 pb-md-4 mx-auto text-center">
                                <p class="fs-5 text-body-secondary">SoVest is designed to make finding reliable stock tips easy, through our proprietary algorithm that tracks users past performance. </p>
                        </div>
                        </header>
                        <main>
                                <div class="row row-cols-1 row-cols-md-1 mb-1 text-center">
                                        <div class="col">
                                        <div class="card mb-4 rounded-3 shadow-sm">
                                                <div class="card-header py-3">
                                                <h4 class="my-0 fw-normal">About SoVest</h4>
                                                </div>
                                                <div class="card-body">
                                                                <p>After becoming interested in investing at an early age, Nate and Nelson started an investment club at their Alma Mater. During this time, WallStreetBets, a subreddit dedicated to sharing stock and option adive and wins was becoming extremely popular due to the Game Stop short squeeze. Before the massive influx of users, genuinely good information and research could be found on WallStreetBets, but with the massive influx of users, it has become more
                                                                         about to Pump and Dump schemes rather than sharing quality information. SoVest has been created to give people looking for quality research a place to go, where it is impossible to fall victim to pump and dumps, because the Contributor's reputation is tied to every post. </p>
                                                        </div>
                                        </div>
                                </div>
                                </div>
                        </main>
                        <footer class="pt-4 my-md-5 pt-md-5 border-top">
                        <div class="row">
                                <div class="col-12 col-md">
                                                <small class="d-block mb-3 text-body-secondary">Created by Nate Pedigo and Nelson Hayslett</small>
                                </div>
                                </div>
                        </footer>
                </div>
                <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
        </body>
</html>