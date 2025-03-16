<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mb-5">
            <h1 class="display-1"><?= $errorCode ?></h1>
            <h2 class="mb-4">Bad Request</h2>
            <p class="lead mb-5">
                <?= $errorMessage ?>
            </p>
            <p class="text-muted mb-4">
                The server couldn't process your request because it contains invalid data.
                Please check your input and try again.
            </p>
            <div class="mt-4 mb-5">
                <a href="/" class="btn btn-primary">Return to Homepage</a>
                <button onclick="history.back()" class="btn btn-outline-secondary ms-2">Go Back</button>
            </div>
        </div>
        <div class="helpful-links mt-5">
            <h3 class="mb-3">Common issues that can cause this error:</h3>
            <div class="card mb-4">
                <div class="card-body text-start">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Missing required form fields</li>
                        <li class="list-group-item">Incorrect data format (numbers, dates, etc.)</li>
                        <li class="list-group-item">Invalid characters in text fields</li>
                        <li class="list-group-item">Malformed URL parameters</li>
                        <li class="list-group-item">Expired form session</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Try Again</h5>
                            <p class="card-text">Return to the previous page and try again</p>
                            <button onclick="history.back()" class="btn btn-sm btn-outline-primary">Go Back</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Contact Support</h5>
                            <p class="card-text">If the problem persists, contact our support team</p>
                            <a href="/about" class="btn btn-sm btn-outline-primary">Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>