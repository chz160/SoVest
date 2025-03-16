<?php
/**
 * SoVest Landing Page
 * 
 * This view displays the landing page with login form for SoVest application.
 */

// Use the app layout for this view
$this->setLayout('app');

// Set view variables
$pageTitle = $pageTitle ?? 'SoVest';
$pageHeader = $pageHeader ?? 'Welcome to SoVest';
$pageSubheader = $pageSubheader ?? 'SoVest aims to bring stock tips to the people, through a fun and innovative platform.';
?>

<div class="row row-cols-1 row-cols-md-1 mb-1 text-center">
    <div class="col">
        <div class="card mb-4 rounded-3 shadow-sm">
            <div class="card-header py-3">
                <h4 class="my-0 fw-normal">SoVest</h4>
            </div>
            <div class="card-body" style="width: 70%; padding-left: 30%;">
                <p>Sign up now to access stock picks from talented individuals and make your own predictions to boost that REP score!</p>
                <form action="/login/submit" method="post">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="tryEmail" name="tryEmail" required>
                        <label for="tryEmail">Email</label>
                    </div>
                    <br>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="tryPass" name="tryPass" required>
                        <label for="tryPass">Password</label>
                    </div>
                    <br>
                    
                    <button class="btn btn-success w-100 py-2" type="submit">Log In</button>
                </form>
                <br>
                <br>
                <p>New to SoVest? <a href="/register">Sign Up Here!</a></p>
            </div>
        </div>
    </div>
</div>