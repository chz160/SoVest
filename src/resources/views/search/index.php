<?php
/**
 * Search page view template
 * 
 * This template displays the search functionality including the search form,
 * results, and sidebar with search history and saved searches.
 */

// Set page-specific CSS and JS
$pageCss = 'css/search.css';
$pageJs = 'js/search.js';
?>

<div class="container search-container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h2 class="text-center mb-4">Search SoVest</h2>
            
            <!-- Main Search Form -->
            <form action="<?= url('search') ?>" method="GET" class="mb-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-lg" 
                           name="query" placeholder="Search for stocks, users, or predictions..." 
                           value="<?= htmlspecialchars($query) ?>"
                           id="searchInput" autocomplete="off">
                    <button class="btn btn-success" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
                
                <!-- Search suggestions container -->
                <div id="searchSuggestions" class="search-suggestions"></div>
                
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <select name="type" class="form-select">
                            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>>All Types</option>
                            <option value="stocks" <?= $type == 'stocks' ? 'selected' : '' ?>>Stocks</option>
                            <option value="predictions" <?= $type == 'predictions' ? 'selected' : '' ?>>Predictions</option>
                            <option value="users" <?= $type == 'users' ? 'selected' : '' ?>>Users</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="prediction" class="form-select">
                            <option value="">Any Prediction</option>
                            <option value="Bullish" <?= $prediction == 'Bullish' ? 'selected' : '' ?>>Bullish</option>
                            <option value="Bearish" <?= $prediction == 'Bearish' ? 'selected' : '' ?>>Bearish</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="sort" class="form-select">
                            <option value="relevance" <?= $sort == 'relevance' ? 'selected' : '' ?>>Relevance</option>
                            <option value="date_desc" <?= $sort == 'date_desc' ? 'selected' : '' ?>>Latest</option>
                            <option value="accuracy" <?= $sort == 'accuracy' ? 'selected' : '' ?>>Highest Accuracy</option>
                            <option value="votes" <?= $sort == 'votes' ? 'selected' : '' ?>>Most Votes</option>
                        </select>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($query) && empty($results)): ?>
                <div class="alert alert-info">
                    No results found for "<?= htmlspecialchars($query) ?>". Try adjusting your search.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Search Results -->
        <div class="col-md-8">
            <?php if (!empty($results)): ?>
                <div class="search-results-header">
                    <h3>Search Results</h3>
                    <p><?= $totalResults ?> result(s) for "<?= htmlspecialchars($query) ?>"</p>
                </div>
                
                <div class="search-results">
                    <?php foreach($results as $result): ?>
                        <div class="search-result-card">
                            <?php if($result['result_type'] == 'stock'): ?>
                                <div class="d-flex align-items-center">
                                    <div class="stock-icon">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                    <div class="result-content">
                                        <h4><?= htmlspecialchars($result['symbol']) ?></h4>
                                        <p><?= htmlspecialchars($result['company_name']) ?></p>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($result['sector']) ?></span>
                                    </div>
                                </div>
                                
                            <?php elseif($result['result_type'] == 'user'): ?>
                                <div class="d-flex align-items-center">
                                    <div class="user-icon">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div class="result-content">
                                        <h4><?= htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) ?></h4>
                                        <p><?= htmlspecialchars($result['email']) ?></p>
                                        <?php if(isset($result['reputation_score'])): ?>
                                            <span class="badge bg-success">REP: <?= $result['reputation_score'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                            <?php elseif($result['result_type'] == 'prediction'): ?>
                                <div class="d-flex align-items-center">
                                    <div class="prediction-icon">
                                        <i class="bi bi-lightning-charge"></i>
                                    </div>
                                    <div class="result-content">
                                        <h4><?= htmlspecialchars($result['symbol']) ?> - 
                                            <span class="<?= $result['prediction_type'] == 'Bullish' ? 'text-success' : 'text-danger' ?>">
                                                <?= htmlspecialchars($result['prediction_type']) ?>
                                            </span>
                                        </h4>
                                        <p>By <?= htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) ?></p>
                                        <div class="d-flex align-items-center mt-2">
                                            <?php if(isset($result['accuracy'])): ?>
                                                <span class="badge me-2 <?= $result['accuracy'] >= 70 ? 'bg-success' : 'bg-warning' ?>">
                                                    Accuracy: <?= $result['accuracy'] ?>%
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge bg-info me-2">
                                                <i class="bi bi-arrow-up"></i> <?= $result['votes'] ?? 0 ?>
                                            </span>
                                            <?php if(isset($result['target_price'])): ?>
                                                <span class="badge bg-secondary">
                                                    Target: $<?= number_format($result['target_price'], 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if($totalResults > 10): ?>
                    <!-- Pagination -->
                    <nav aria-label="Search results pagination">
                        <ul class="pagination justify-content-center">
                            <?php 
                                $totalPages = ceil($totalResults / 10);
                                for($i = 1; $i <= $totalPages; $i++):
                            ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('search') ?>?query=<?= urlencode($query) ?>&type=<?= $type ?>&prediction=<?= $prediction ?>&sort=<?= $sort ?>&page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php elseif(empty($query)): ?>
                <div class="empty-search-message text-center py-5">
                    <i class="bi bi-search" style="font-size: 3rem;"></i>
                    <h3 class="mt-3">Start your search above</h3>
                    <p>Search for stocks, predictions, or other users</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Search History & Saved Searches Sidebar -->
        <div class="col-md-4">
            <?php if (!empty($searchHistory)): ?>
                <div class="card bg-dark mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Search History</h5>
                        <button class="btn btn-sm btn-outline-secondary" id="clearHistory">Clear</button>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php foreach($searchHistory as $history): ?>
                                <li class="list-group-item bg-transparent border-light">
                                    <a href="<?= url('search') ?>?query=<?= urlencode($history['search_query']) ?>&type=<?= $history['search_type'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($history['search_query']) ?>
                                        <small class="text-muted d-block">
                                            <?= ucfirst($history['search_type']) ?> • 
                                            <?= date("M j, g:i a", strtotime($history['created_at'])) ?>
                                        </small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($savedSearches)): ?>
                <div class="card bg-dark">
                    <div class="card-header">
                        <h5 class="mb-0">Saved Searches</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php foreach($savedSearches as $saved): ?>
                                <li class="list-group-item bg-transparent border-light d-flex justify-content-between align-items-center">
                                    <a href="<?= url('search') ?>?query=<?= urlencode($saved['search_query']) ?>&type=<?= $saved['search_type'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($saved['search_query']) ?>
                                        <small class="text-muted d-block">
                                            <?= ucfirst($saved['search_type']) ?>
                                        </small>
                                    </a>
                                    <button class="btn btn-sm btn-danger remove-saved" data-id="<?= $saved['id'] ?>">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($query)): ?>
                <div class="mt-4 text-center">
                    <button id="saveSearch" class="btn btn-outline-success" data-query="<?= htmlspecialchars($query) ?>" data-type="<?= $type ?>">
                        <i class="bi bi-bookmark-plus"></i> Save This Search
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>