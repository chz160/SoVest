<?php
/**
 * Prediction Creation/Edit View
 * 
 * This view displays the form for creating or editing stock predictions.
 */

// Use the app layout for this view
$this->setLayout('app');

// Set view variables
$pageTitle = $pageTitle ?? ($isEditing ? 'Edit Prediction' : 'Create Prediction');
$pageHeader = $pageHeader ?? ($isEditing ? 'Edit Your Stock Prediction' : 'Create New Stock Prediction');
$pageSubheader = $pageSubheader ?? 'Make your market prediction and build your reputation';

// Set page JS
$pageJs = 'js/prediction/prediction.js';
?>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <form id="prediction-form" action="<?= $isEditing ? '/prediction/update' : '/prediction/store' ?>" method="post">
                    <?php if ($isEditing): ?>
                        <input type="hidden" name="prediction_id" value="<?= $prediction['prediction_id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="stock-search" class="form-label">Stock Symbol</label>
                        <?php if ($isEditing): ?>
                            <input type="text" class="form-control" id="stock-search" 
                                value="<?= htmlspecialchars($prediction['symbol'] . ' - ' . $prediction['company_name']) ?>" readonly>
                            <input type="hidden" id="stock_id" name="stock_id" value="<?= $prediction['stock_id'] ?>" required>
                        <?php else: ?>
                            <input type="text" class="form-control" id="stock-search" placeholder="Search for a stock symbol or name...">
                            <div id="stock-suggestions" class="mt-2"></div>
                            <input type="hidden" id="stock_id" name="stock_id" required>
                            <?php if (isset($errors['stock_id'])): ?>
                                <div class="invalid-feedback d-block">
                                    <?= $errors['stock_id'][0] ?? 'Please select a valid stock' ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prediction_type" class="form-label">Prediction Type</label>
                        <select class="form-select <?= isset($errors['prediction_type']) ? 'is-invalid' : '' ?>" 
                                id="prediction_type" name="prediction_type" required>
                            <option value="" <?= !$isEditing || !isset($prediction['prediction_type']) ? 'selected' : '' ?> disabled>Select prediction type</option>
                            <option value="Bullish" <?= $isEditing && $prediction['prediction_type'] == 'Bullish' ? 'selected' : '' ?>>
                                Bullish (Stock will rise)
                            </option>
                            <option value="Bearish" <?= $isEditing && $prediction['prediction_type'] == 'Bearish' ? 'selected' : '' ?>>
                                Bearish (Stock will fall)
                            </option>
                        </select>
                        <?php if (isset($errors['prediction_type'])): ?>
                            <div class="invalid-feedback">
                                <?= $errors['prediction_type'][0] ?? 'Please select a prediction type' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="target_price" class="form-label">Target Price (optional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control <?= isset($errors['target_price']) ? 'is-invalid' : '' ?>" 
                                   id="target_price" name="target_price" step="0.01" min="0" 
                                   value="<?= $isEditing && isset($prediction['target_price']) ? htmlspecialchars($prediction['target_price']) : '' ?>">
                        </div>
                        <?php if (isset($errors['target_price'])): ?>
                            <div class="invalid-feedback">
                                <?= $errors['target_price'][0] ?>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Your predicted price target for this stock</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Timeframe (End Date)</label>
                        <input type="date" class="form-control <?= isset($errors['end_date']) ? 'is-invalid' : '' ?>" 
                               id="end_date" name="end_date" required
                               value="<?= $isEditing ? date('Y-m-d', strtotime($prediction['end_date'])) : '' ?>">
                        <?php if (isset($errors['end_date'])): ?>
                            <div class="invalid-feedback">
                                <?= $errors['end_date'][0] ?? 'Please select a valid future date' ?>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">When do you expect your prediction to be fulfilled?</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reasoning" class="form-label">Reasoning</label>
                        <textarea class="form-control <?= isset($errors['reasoning']) ? 'is-invalid' : '' ?>" 
                                  id="reasoning" name="reasoning" rows="4" required><?= $isEditing ? htmlspecialchars($prediction['reasoning']) : '' ?></textarea>
                        <?php if (isset($errors['reasoning'])): ?>
                            <div class="invalid-feedback">
                                <?= $errors['reasoning'][0] ?? 'Please provide reasoning for your prediction' ?>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Explain why you believe this prediction will come true</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?= $isEditing ? 'Update' : 'Create' ?> Prediction
                        </button>
                        <a href="/my_predictions" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Tips for Great Predictions</h5>
                <ul class="card-text">
                    <li>Be specific about your reasoning</li>
                    <li>Include relevant market factors and trends</li>
                    <li>Consider recent news and company announcements</li>
                    <li>Set a realistic timeframe for your prediction</li>
                    <li>Include a target price for more precise evaluation</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Additional date validation
document.addEventListener('DOMContentLoaded', function() {
    const endDateInput = document.getElementById('end_date');
    if (endDateInput) {
        // Set minimum date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        endDateInput.min = tomorrow.toISOString().split('T')[0];
        
        // Validate that the date is in the future
        endDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const currentDate = new Date();
            
            if (selectedDate <= currentDate) {
                this.setCustomValidity('End date must be in the future');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>