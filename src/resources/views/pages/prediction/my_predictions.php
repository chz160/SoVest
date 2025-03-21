<?php
// Set the title and include CSS/JS for the layout
$pageTitle = $pageTitle ?? 'My Predictions';
$pageCss = '<link rel="stylesheet" href="/css/prediction.css">';
$pageJs = '<script src="/js/prediction/prediction.js"></script>';

// Start capturing the content
ob_start();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <a href="<?php echo sovest_route('predictions.create'); ?>" class="btn btn-primary">Create New Prediction</a>
    </div>

    <?php if (empty($predictions)): ?>
        <div class="empty-state prediction-card">
            <h4>No predictions yet</h4>
            <p>You haven't made any stock predictions yet. Create your first prediction to start building your reputation!</p>
            <a href="<?php echo sovest_route('predictions.create'); ?>" class="btn btn-primary mt-3">Create Your First Prediction</a>
        </div>
    <?php else: ?>
        <?php foreach ($predictions as $prediction): ?>
            <div class="prediction-card">
                <div class="prediction-header">
                    <h4><?php echo htmlspecialchars($prediction['symbol']); ?> - <?php echo htmlspecialchars($prediction['company_name']); ?></h4>
                    <span class="badge <?php echo $prediction['prediction_type'] == 'Bullish' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo htmlspecialchars($prediction['prediction_type']); ?>
                    </span>
                </div>
                <div class="prediction-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($prediction['prediction_date'])); ?></p>
                            <p><strong>End Date:</strong> <?php echo date('M j, Y', strtotime($prediction['end_date'])); ?></p>
                            <?php if (!empty($prediction['target_price'])): ?>
                                <p><strong>Target Price:</strong> $<?php echo htmlspecialchars(number_format($prediction['target_price'], 2)); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php 
                            $statusClass = 'bg-secondary';
                            $statusText = 'Inactive';
                            
                            if ($prediction['is_active'] == 1) {
                                if (strtotime($prediction['end_date']) > time()) {
                                    $statusClass = 'bg-primary';
                                    $statusText = 'Active';
                                } else {
                                    $statusClass = 'bg-warning text-dark';
                                    $statusText = 'Expired';
                                }
                            }
                            ?>
                            <p>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </p>
                            <p><strong>Upvotes:</strong> <?php echo isset($prediction['votes']) ? $prediction['votes'] : 0; ?></p>
                            <?php if (isset($prediction['accuracy']) && $prediction['accuracy'] !== null): ?>
                                <p><strong>Accuracy:</strong> <?php echo htmlspecialchars(number_format($prediction['accuracy'], 2)); ?>%</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($prediction['reasoning'])): ?>
                        <div class="reasoning mt-3">
                            <h5>Reasoning:</h5>
                            <p><?php echo htmlspecialchars($prediction['reasoning']); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($prediction['is_active'] == 1 && strtotime($prediction['end_date']) > time()): ?>
                        <div class="action-buttons mt-3">
                            <a href="<?php echo sovest_route('predictions.edit', ['id' => $prediction['prediction_id']]); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <button class="btn btn-sm btn-outline-danger delete-prediction" 
                                   data-id="<?php echo $prediction['prediction_id']; ?>" 
                                   data-bs-toggle="modal" 
                                   data-bs-target="#deleteModal">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
// Update API endpoint for prediction.js to use Laravel routes
const apiEndpoints = {
    deletePrediction: '<?php echo sovest_route('api.predictions.delete'); ?>',
    searchStocks: '<?php echo sovest_route('api.search_stocks'); ?>'
};
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';
?>