<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mb-5">
            <h1 class="display-1"><?= $errorCode ?></h1>
            <h2 class="mb-4">Server Error</h2>
            <p class="lead mb-5">
                <?= $errorMessage ?>
            </p>
            <p class="text-muted mb-4">
                Our technical team has been notified and is working to resolve the issue.
                Please try again later.
            </p>
            <div class="mt-4 mb-5">
                <a href="/" class="btn btn-primary">Return to Homepage</a>
                <button onclick="history.back()" class="btn btn-outline-secondary ms-2">Go Back</button>
            </div>
        </div>
        <div class="helpful-links mt-5">
            <h3 class="mb-3">In the meantime, you can try:</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Refresh the Page</h5>
                            <p class="card-text">Sometimes a simple refresh can fix temporary issues</p>
                            <button onclick="location.reload()" class="btn btn-sm btn-outline-primary">Refresh</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Clear Cache</h5>
                            <p class="card-text">Clear your browser cache and cookies</p>
                            <button onclick="history.back()" class="btn btn-sm btn-outline-primary">Go Back</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Contact Support</h5>
                            <p class="card-text">Report this issue to our support team</p>
                            <a href="/about" class="btn btn-sm btn-outline-primary">Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>