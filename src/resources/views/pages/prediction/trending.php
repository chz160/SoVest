<?php
// Set the title and include CSS/JS for the layout
$pageTitle = $pageTitle ?? 'Trending Predictions';
$pageCss = '<link rel="stylesheet" href="/css/prediction.css">';
$pageJs = '<script src="/js/scoring.js"></script>';

// Start capturing the content
ob_start();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <a href="<?php echo sovest_route('predictions.create'); ?>" class="btn btn-primary">Create New Prediction</a>
    </div>

    <?php if (empty($trending_predictions)): ?>
        <div class="empty-state prediction-card">
            <h4>No trending predictions</h4>
            <p>There are no trending predictions at this time. Be the first to make a prediction!</p>
            <a href="<?php echo sovest_route('predictions.create'); ?>" class="btn btn-primary mt-3">Create Your First Prediction</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($trending_predictions as $prediction): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="prediction-card">
                        <div class="prediction-header">
                            <h4><?php echo htmlspecialchars($prediction['symbol']); ?></h4>
                            <span class="badge <?php echo $prediction['prediction'] == 'Bullish' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo htmlspecialchars($prediction['prediction']); ?>
                            </span>
                        </div>
                        <div class="prediction-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <p class="mb-1"><strong>Predictor:</strong> <?php echo htmlspecialchars($prediction['username']); ?></p>
                                    <p class="mb-1"><strong>Reputation:</strong> <span class="<?php echo $prediction['reputation_score'] >= 50 ? 'text-success' : ($prediction['reputation_score'] >= 20 ? 'text-info' : 'text-warning'); ?>"><?php echo htmlspecialchars($prediction['reputation_score']); ?></span></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-7">
                                    <?php if (!empty($prediction['target_price'])): ?>
                                        <p class="mb-1"><strong>Target:</strong> $<?php echo htmlspecialchars(number_format($prediction['target_price'], 2)); ?></p>
                                    <?php endif; ?>
                                    <p class="mb-1"><strong>End Date:</strong> <?php echo date('M j, Y', strtotime($prediction['end_date'])); ?></p>
                                </div>
                                <div class="col-5 text-end">
                                    <p class="mb-1"><strong>Votes:</strong> <?php echo $prediction['votes']; ?></p>
                                    <?php if (isset($prediction['accuracy'])): ?>
                                        <p class="mb-1">
                                            <strong>Accuracy:</strong> 
                                            <?php echo renderPredictionBadge($prediction['accuracy']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo sovest_route('predictions.view', ['id' => $prediction['prediction_id']]); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                <button class="btn btn-sm btn-outline-success vote-button" 
                                       data-id="<?php echo $prediction['prediction_id']; ?>"
                                       data-vote-type="upvote">
                                    <i class="bi bi-hand-thumbs-up"></i> Upvote
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Set API endpoint for voting
const voteApiEndpoint = '<?php echo sovest_route('api.predictions.vote'); ?>';

// Initialize scoring visualizations
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initScoring === 'function') {
        initScoring();
    }
    
    // Setup vote buttons
    const voteButtons = document.querySelectorAll('.vote-button');
    voteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const predictionId = this.getAttribute('data-id');
            const voteType = this.getAttribute('data-vote-type');
            
            fetch(voteApiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `prediction_id=${predictionId}&vote_type=${voteType}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to show updated vote count
                    location.reload();
                } else {
                    alert(data.message || 'Error recording vote');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to record vote. Please try again.');
            });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>