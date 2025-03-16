<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mb-5">
            <h1 class="display-1"><?= $errorCode ?></h1>
            <h2 class="mb-4">Access Denied</h2>
            <p class="lead mb-5">
                <?= $errorMessage ?>
            </p>
            <p class="text-muted mb-4">
                You do not have sufficient permissions to access this page.
                If you believe this is an error, please contact support.
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
                            <h5 class="card-title">Login</h5>
                            <p class="card-text">Login with an account that has the required permissions</p>
                            <a href="/login" class="btn btn-sm btn-outline-primary">Login</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Register</h5>
                            <p class="card-text">Create a new account to access SoVest</p>
                            <a href="/register" class="btn btn-sm btn-outline-primary">Register</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Public Content</h5>
                            <p class="card-text">Browse publicly accessible content</p>
                            <a href="/predictions" class="btn btn-sm btn-outline-primary">Predictions</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>