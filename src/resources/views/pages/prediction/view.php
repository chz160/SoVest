<?php
/**
 * Prediction View Template
 * 
 * This view displays a single prediction with all its details.
 */

// Use the app layout for this view
$this->setLayout('app');

// Set view variables
$pageTitle = $pageTitle ?? "{$prediction['symbol']} {$prediction['prediction_type']} Prediction";
$pageHeader = $pageHeader ?? "Stock Prediction Details";
$pageSubheader = $pageSubheader ?? "User prediction for {$prediction['symbol']}";

// Include prediction score display functions
require_once __DIR__ . '/../../../includes/prediction_score_display.php';

// Get authenticated user
$currentUser = $this->getAuthUser();
$isOwner = $currentUser && $currentUser['id'] == $prediction['user_id'];

// Calculate prediction status
$isPending = $prediction['accuracy'] === null;
$isActive = $prediction['is_active'] == 1;
$endDate = new DateTime($prediction['end_date']);
$today = new DateTime();
$daysRemaining = $today > $endDate ? 0 : $today->diff($endDate)->days;

// Generate prediction class and icon
$predictionClass = $prediction['prediction_type'] == 'Bullish' ? 'text-success' : 'text-danger';
$predictionIcon = $prediction['prediction_type'] == 'Bullish' ? 
    '<i class="bi bi-graph-up-arrow"></i>' : 
    '<i class="bi bi-graph-down-arrow"></i>';

// Generate badge for accuracy
$accuracyBadge = renderPredictionBadge($prediction['accuracy']);

// Determine user's existing vote (if any)
$userVoted = false;
$userVoteType = null;
if ($currentUser) {
    foreach ($prediction['votes'] as $vote) {
        if ($vote['user_id'] == $currentUser['id']) {
            $userVoted = true;
            $userVoteType = $vote['vote_type'];
            break;
        }
    }
}
?>

<div class="row">
    <!-- Main prediction content -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <span class="<?= $predictionClass ?>"><?= $predictionIcon ?> <?= htmlspecialchars($prediction['prediction_type']) ?></span>
                    on <strong><?= htmlspecialchars($prediction['symbol']) ?></strong>
                </h3>
                <div>
                    <?= $accuracyBadge ?>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Prediction details -->
                <div class="mb-4">
                    <h5>Prediction by <?= htmlspecialchars($prediction['username']) ?></h5>
                    <div class="text-muted mb-3">
                        <small>
                            Created: <?= date('M j, Y', strtotime($prediction['prediction_date'])) ?> • 
                            <?php if ($isPending): ?>
                                Ends: <?= date('M j, Y', strtotime($prediction['end_date'])) ?>
                                (<?= $daysRemaining ?> days remaining)
                            <?php else: ?>
                                Ended: <?= date('M j, Y', strtotime($prediction['end_date'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <?php if ($prediction['target_price']): ?>
                    <div class="mb-3">
                        <h6>Target Price:</h6>
                        <p class="fs-4 <?= $predictionClass ?>">$<?= number_format($prediction['target_price'], 2) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6>Reasoning:</h6>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($prediction['reasoning'])) ?>
                        </div>
                    </div>
                </div>
                
                <!-- Voting section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Prediction Voting</h5>
                        <p class="text-muted">Do you agree with this prediction?</p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <button class="btn <?= $userVoteType == 'upvote' ? 'btn-success' : 'btn-outline-success' ?> me-2 vote-btn" 
                                        data-prediction-id="<?= $prediction['prediction_id'] ?>" 
                                        data-vote-type="upvote">
                                    <i class="bi bi-hand-thumbs-up"></i> Agree
                                </button>
                                <span class="badge bg-success ms-1"><?= $prediction['upvotes'] ?></span>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <button class="btn <?= $userVoteType == 'downvote' ? 'btn-danger' : 'btn-outline-danger' ?> me-2 vote-btn" 
                                        data-prediction-id="<?= $prediction['prediction_id'] ?>" 
                                        data-vote-type="downvote">
                                    <i class="bi bi-hand-thumbs-down"></i> Disagree
                                </button>
                                <span class="badge bg-danger ms-1"><?= $prediction['downvotes'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Owner actions -->
                <?php if ($isOwner && $isActive): ?>
                <div class="d-flex justify-content-end">
                    <a href="/prediction/edit?id=<?= $prediction['prediction_id'] ?>" 
                       class="btn btn-outline-primary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-outline-danger delete-prediction" 
                            data-id="<?= $prediction['prediction_id'] ?>" 
                            data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back button -->
        <div class="mb-4">
            <a href="/trending" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Trending
            </a>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Stock information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Stock Information</h5>
            </div>
            <div class="card-body">
                <h3><?= htmlspecialchars($prediction['stock']['symbol']) ?></h3>
                <p class="text-muted"><?= htmlspecialchars($prediction['stock']['company_name']) ?></p>
                
                <?php if (isset($prediction['stock']['sector'])): ?>
                <div class="mb-3">
                    <strong>Sector:</strong> <?= htmlspecialchars($prediction['stock']['sector']) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($prediction['stock']['current_price'])): ?>
                <div class="mb-3">
                    <strong>Current Price:</strong> $<?= number_format($prediction['stock']['current_price'], 2) ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="/search?symbol=<?= urlencode($prediction['stock']['symbol']) ?>" class="btn btn-outline-primary btn-sm">
                        View Stock Details
                    </a>
                </div>
            </div>
        </div>
        
        <!-- User information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Predictor Profile</h5>
            </div>
            <div class="card-body">
                <h5><?= htmlspecialchars($prediction['username']) ?></h5>
                
                <!-- Display user reputation if available -->
                <?php if (isset($prediction['user']['reputation_score'])): ?>
                    <?= renderReputationScore($prediction['user']['reputation_score']) ?>
                <?php endif; ?>
                
                <!-- If user has other predictions, show link -->
                <div class="mt-3">
                    <a href="/user/predictions?id=<?= $prediction['user_id'] ?>" class="btn btn-outline-secondary btn-sm">
                        View User's Predictions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this prediction? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle voting
document.addEventListener('DOMContentLoaded', function() {
    const voteButtons = document.querySelectorAll('.vote-btn');
    
    voteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const predictionId = this.getAttribute('data-prediction-id');
            const voteType = this.getAttribute('data-vote-type');
            
            // Create form data
            const formData = new FormData();
            formData.append('prediction_id', predictionId);
            formData.append('vote_type', voteType);
            
            // Send vote request
            fetch('/prediction/vote', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update vote counts
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting vote:', error);
                alert('An error occurred while submitting your vote');
            });
        });
    });
});
</script>