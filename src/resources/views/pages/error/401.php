<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mb-5">
            <h1 class="display-1"><?= $errorCode ?></h1>
            <h2 class="mb-4">Authentication Required</h2>
            <p class="lead mb-5">
                <?= $errorMessage ?>
            </p>
            <p class="text-muted mb-4">
                Please login to access this resource. If you don't have an account,
                you can register for free.
            </p>
            <div class="mt-4 mb-5">
                <a href="/login" class="btn btn-primary">Login</a>
                <a href="/register" class="btn btn-secondary ms-2">Register</a>
            </div>
        </div>
        <div class="helpful-links mt-5">
            <h3 class="mb-3">While you're here, you can:</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Explore SoVest</h5>
                            <p class="card-text">Learn more about our platform</p>
                            <a href="/about" class="btn btn-sm btn-outline-primary">About</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">View Leaderboard</h5>
                            <p class="card-text">See our top performers</p>
                            <a href="/leaderboard" class="btn btn-sm btn-outline-primary">Leaderboard</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Browse Trending</h5>
                            <p class="card-text">Check out trending predictions</p>
                            <a href="/trending" class="btn btn-sm btn-outline-primary">Trending</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>