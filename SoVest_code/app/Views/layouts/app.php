<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?? 'SoVest' ?></title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">   

    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="manifest" href="/images/site.webmanifest">
    
    <?php if (isset($pageCss)): ?>
    <link href="/<?= $pageCss ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <div class="container py-3">
        <header>
            <div class="d-flex flex-column flex-md-row align-items-center pb-3 mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center link-body-emphasis text-decoration-none">
                    <span class="fs-4">SoVest</span>
                </a>

                <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
                    <?php if (isset($user)): ?>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/home">Home</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/account">My Account</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/logout">Logout</a>
                    <?php else: ?>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/home">Home</a>
                    <a class="me-3 py-2 link-body-emphasis text-decoration-none" href="/about">About SoVest</a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <?php if (!empty($pageHeader)): ?>
            <div class="pricing-header p-3 pb-md-4 mx-auto text-center">
                <h1 class="display-4 fw-normal"><?= $pageHeader ?></h1>
                <?php if (!empty($pageSubheader)): ?>
                <p class="fs-5 text-body-secondary"><?= $pageSubheader ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </header>

        <main>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success) && !empty($message)): ?>
                <div class="alert alert-success">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <?= $content ?? '' ?>
        </main>

        <footer class="pt-4 my-md-5 pt-md-5 border-top">
            <div class="row">
                <div class="col-12 col-md">
                    <small class="d-block mb-3 text-body-secondary">Created by Nate Pedigo, Nelson Hayslett</small>
                </div>
            </div>
        </footer>
    </div>
    
    <script src="/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($pageJs)): ?>
    <script src="/<?= $pageJs ?>"></script>
    <?php endif; ?>
</body>
</html>
