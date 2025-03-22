<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mb-5">
            <h1 class="display-1"><?= $errorCode ?></h1>
            <h2 class="mb-4"><?= $errorMessage ?></h2>
            <p class="lead mb-5">
                The page you are looking for might have been removed, had its name changed, 
                or is temporarily unavailable.
            </p>
            <div class="mt-4 mb-5">
                <a href="/" class="btn btn-primary">Return to Homepage</a>
                <button onclick="history.back()" class="btn btn-outline-secondary ms-2">Go Back</button>
            </div>
        </div>
        <div class="helpful-links mt-5">
            <h3 class="mb-3">You might want to try:</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Search</h5>
                            <p class="card-text">Try searching for what you're looking for</p>
                            <a href="/search" class="btn btn-sm btn-outline-primary">Search</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Predictions</h5>
                            <p class="card-text">View latest stock predictions</p>
                            <a href="/predictions" class="btn btn-sm btn-outline-primary">Predictions</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">About</h5>
                            <p class="card-text">Learn more about SoVest</p>
                            <a href="/about" class="btn btn-sm btn-outline-primary">About</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>