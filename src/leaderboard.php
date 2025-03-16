<?php
/**
 * Leaderboard Page
 * 
 * This page shows the top-performing users on SoVest.
 */

     /*
        TODO: We need to validate that this page has been fully converted to the Laravel
        framework controllers, views, and routes before we delete it.
    */

session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) || isset($_COOKIE["userID"]);
if ($isLoggedIn) {
    $userID = $_SESSION['user_id'] ?? $_COOKIE["userID"];
}

// Include the prediction scoring service and ServiceFactory
require_once 'bootstrap/database.php';
require_once 'services/PredictionScoringService.php';
require_once 'app/Services/ServiceFactory.php';

// Create an instance of the service using ServiceFactory
$scoringService = App\Services\ServiceFactory::createPredictionScoringService();

// Get top users from the service
$topUsers = $scoringService->getTopUsers(20);

// Get performance stats for a specific user
if (isset($_GET['user_id'])) {
    $selectedUserId = (int) $_GET['user_id'];
    $userStats = $scoringService->getUserPredictionStats($selectedUserId);
}

// Load header
$pageTitle = "Leaderboard";
include('includes/header.php');
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="text-center mb-4">SoVest Leaderboard</h2>
            <p class="text-center">Top performing analysts based on prediction accuracy and reputation</p>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card bg-dark">
                <div class="card-header">
                    <h4>Top Analysts</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Analyst</th>
                                    <th>Reputation</th>
                                    <th>Predictions</th>
                                    <th>Avg. Accuracy</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($topUsers as $user): 
                                ?>
                                    <tr <?php echo (isset($selectedUserId) && $user['id'] == $selectedUserId) ? 'class="table-primary"' : ''; ?>>
                                        <td><?php echo $rank; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo $user['reputation_score']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['predictions_count']; ?></td>
                                        <td>
                                            <?php if (isset($user['avg_accuracy']) && $user['avg_accuracy'] !== null): ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar 
                                                        <?php
                                                            if ($user['avg_accuracy'] >= 70) echo 'bg-success';
                                                            else if ($user['avg_accuracy'] >= 50) echo 'bg-info';
                                                            else if ($user['avg_accuracy'] >= 30) echo 'bg-warning';
                                                            else echo 'bg-danger';
                                                        ?>"
                                                        role="progressbar"
                                                        style="width: <?php echo $user['avg_accuracy']; ?>%"
                                                        aria-valuenow="<?php echo $user['avg_accuracy']; ?>"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        <?php echo round($user['avg_accuracy'], 1); ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No data</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-info">View Stats</a>
                                        </td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <?php if (isset($selectedUserId) && isset($userStats)): ?>
                <div class="card bg-dark">
                    <div class="card-header">
                        <h4>Analyst Performance</h4>
                    </div>
                    <div class="card-body">
                        <!-- Display selected user's stats -->
                        <?php 
                        // Find the selected user in the top users list
                        $selectedUser = null;
                        foreach ($topUsers as $user) {
                            if ($user['id'] == $selectedUserId) {
                                $selectedUser = $user;
                                break;
                            }
                        }
                        
                        if ($selectedUser): 
                        ?>
                            <h5 class="mb-3"><?php echo htmlspecialchars($selectedUser['first_name'] . ' ' . $selectedUser['last_name']); ?></h5>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Reputation Score:</strong></p>
                                <h3>
                                    <span class="badge bg-success"><?php echo $userStats['reputation']; ?></span>
                                </h3>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Prediction Accuracy:</strong></p>
                                <div class="progress mb-2" style="height: 24px;">
                                    <div class="progress-bar 
                                        <?php
                                            if ($userStats['avg_accuracy'] >= 70) echo 'bg-success';
                                            else if ($userStats['avg_accuracy'] >= 50) echo 'bg-info';
                                            else if ($userStats['avg_accuracy'] >= 30) echo 'bg-warning';
                                            else echo 'bg-danger';
                                        ?>"
                                        role="progressbar"
                                        style="width: <?php echo $userStats['avg_accuracy']; ?>%"
                                        aria-valuenow="<?php echo $userStats['avg_accuracy']; ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                        <?php echo $userStats['avg_accuracy']; ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="card bg-secondary text-center p-2">
                                        <h5 class="m-0"><?php echo $userStats['total']; ?></h5>
                                        <small>Total Predictions</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-center p-2">
                                        <h5 class="m-0"><?php echo $userStats['accurate']; ?></h5>
                                        <small>Accurate</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="card bg-danger text-center p-2">
                                        <h5 class="m-0"><?php echo $userStats['inaccurate']; ?></h5>
                                        <small>Inaccurate</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-warning text-center p-2">
                                        <h5 class="m-0"><?php echo $userStats['pending']; ?></h5>
                                        <small>Pending</small>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="user_profile.php?id=<?php echo $selectedUserId; ?>" class="btn btn-primary w-100">
                                View Full Profile
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning">User information not found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-dark">
                    <div class="card-header">
                        <h4>Leaderboard Info</h4>
                    </div>
                    <div class="card-body">
                        <p>The SoVest leaderboard ranks users based on their prediction accuracy and overall reputation score.</p>
                        <p>Users gain reputation points for accurate predictions and lose points for inaccurate ones.</p>
                        <p>Click on "View Stats" to see detailed performance metrics for any analyst.</p>
                        
                        <?php if ($isLoggedIn): ?>
                            <div class="alert alert-info">
                                <strong>Improve your ranking!</strong><br>
                                Make accurate predictions to climb the leaderboard.
                            </div>
                            <a href="create_prediction.php" class="btn btn-success w-100">
                                Make a Prediction
                            </a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <strong>Want to join the rankings?</strong><br>
                                Sign up and start making predictions!
                            </div>
                            <a href="signup.php" class="btn btn-success w-100">
                                Sign Up Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>