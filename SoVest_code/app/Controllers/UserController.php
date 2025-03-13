<?php

namespace App\Controllers;

use Database\Models\User;
use Database\Models\Prediction;
use Exception;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\PredictionScoringServiceInterface;
use App\Services\ServiceFactory;

/**
 * User Controller
 * 
 * Handles user account management, login, registration, and related functionality.
 */
class UserController extends Controller
{
    /**
     * @var PredictionScoringServiceInterface Prediction scoring service
     */
    protected $scoringService;

    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (optional)
     * @param PredictionScoringServiceInterface|null $scoringService Prediction scoring service (optional)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(AuthServiceInterface $authService = null, PredictionScoringServiceInterface $scoringService = null, array $services = [])
    {
        parent::__construct($authService, $services);
        
        // Initialize scoring service with dependency injection
        $this->scoringService = $scoringService;
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->scoringService === null) {
            $this->scoringService = ServiceFactory::createPredictionScoringService();
        }
    }
    
    /**
     * Display user account page
     * 
     * @return void
     */
    public function account()
    {
        // Require authentication
        $this->requireAuth();
        
        // Get current user data
        $userData = $this->getAuthUser();
        $userID = $userData['id'];
        
        // Use the injected scoring service to get user stats
        $userStats = $this->scoringService->getUserPredictionStats($userID);
        
        try {
            // Get user predictions with related stock data
            $predictionModels = Prediction::with('stock')
                ->where('user_id', $userID)
                ->orderBy('prediction_date', 'DESC')
                ->limit(5)
                ->get();
            
            $predictions = [];
            
            if ($predictionModels->count() > 0) {
                foreach ($predictionModels as $prediction) {
                    $row = [
                        'prediction_id' => $prediction->prediction_id,
                        'symbol' => $prediction->stock->symbol,
                        'prediction' => $prediction->prediction_type,
                        'accuracy' => $prediction->accuracy,
                        'target_price' => $prediction->target_price,
                        'end_date' => $prediction->end_date,
                        'is_active' => $prediction->is_active
                    ];
                    
                    // Keep the raw accuracy value for styling
                    $row['raw_accuracy'] = $row['accuracy'];
                    
                    // Format accuracy as percentage if not null
                    if ($row['accuracy'] !== null) {
                        $row['accuracy'] = number_format($row['accuracy'], 0) . '%';
                    } else {
                        $row['accuracy'] = 'Pending';
                    }
                    
                    $predictions[] = $row;
                }
            }
        } catch (Exception $e) {
            // Error handling
            $this->withError('Error fetching predictions: ' . $e->getMessage());
            $predictions = [];
        }
        
        // Prepare user data for display
        $user = [
            'username' => $userData['email'],
            'full_name' => ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''),
            'bio' => $userData['major'] ? $userData['major'] . ' | ' . $userData['year'] : 'Stock enthusiast',
            'profile_picture' => 'images/logo.png',
            'reputation_score' => isset($userData['reputation_score']) ? $userData['reputation_score'] : 0,
            'avg_accuracy' => $userStats['avg_accuracy'],
            'predictions' => $predictions
        ];
        
        // Set page title
        $pageTitle = 'My Account';
        $pageCss = 'css/prediction.css';
        $pageJs = 'js/scoring.js';
        
        // Render the view
        $this->render('user/account', [
            'user' => $user,
            'userStats' => $userStats,
            'pageTitle' => $pageTitle,
            'pageCss' => $pageCss,
            'pageJs' => $pageJs
        ]);
    }
    
    /**
     * Display leaderboard page
     * 
     * @return void
     */
    public function leaderboard()
    {
        // Use the injected scoring service to get top users
        $topUsers = $this->scoringService->getTopUsers(10);
        
        // Set page title
        $pageTitle = 'Leaderboard';
        
        // Render the view
        $this->render('leaderboard', [
            'topUsers' => $topUsers,
            'pageTitle' => $pageTitle
        ]);
    }
    
    /**
     * Handle user registration form submission
     * 
     * @return void
     */
    public function register()
    {
        // Extract the form data
        $newEmail = $this->input('newEmail', '');
        $newPass = $this->input('newPass', '');
        $newMajor = $this->input('newMajor', '');
        $newYear = $this->input('newYear', '');
        $newScholarship = $this->input('newScholarship', '');
        
        try {
            // Create a new User model instance with form data
            $user = new User();
            $user->email = $newEmail;
            $user->password = $newPass; // Plain password, will be hashed if validation passes
            $user->major = $newMajor;
            $user->year = $newYear;
            $user->scholarship = $newScholarship;
            
            // Validate the user model using the validation rules
            if (!$user->validate()) {
                // Handle validation errors by checking which fields failed
                $errors = $user->getErrors();
                
                if (isset($errors['email'])) {
                    return $this->redirect('/register', ['error' => 'invalid_email']);
                } elseif (isset($errors['password'])) {
                    return $this->redirect('/register', ['error' => 'password_too_short']);
                } else {
                    // Generic error for other validation failures
                    return $this->redirect('/register', ['error' => 'validation_failed']);
                }
            }
            
            // Hash the password before saving
            $user->password = password_hash($newPass, PASSWORD_DEFAULT);
            
            // Save the validated user
            $user->save();
            
            // Redirect to the login page with success message
            return $this->redirect('/login', ['success' => 1]);
            
        } catch (Exception $e) {
            // Log the error
            error_log("Account creation error: " . $e->getMessage());
            
            // Redirect to account creation page with error
            return $this->redirect('/register', ['error' => 'system_error']);
        }
    }
    
    /**
     * Handle user login form submission
     * 
     * @return void
     */
    public function login()
    {
        // Extract the form data
        $tryEmail = $this->input('tryEmail', '');
        $tryPass = $this->input('tryPass', '');
        
        try {
            // Find the user by email using Eloquent
            $user = User::where('email', $tryEmail)->first();
            
            // If user not found, redirect to login page
            if (!$user) {
                return $this->redirect('index.php');
            }
            
            // If user found, compare the password securely
            // First try with password_verify() for hashed passwords
            if (password_verify($tryPass, $user->password)) {
                setcookie("userID", $user->id, time() + (86400 * 30), "/"); // Sets cookie for 30 days
                return $this->redirect('home.php');
            } 
            // For backward compatibility, check if the password matches plaintext (old method)
            else if ($tryPass == $user->password) {
                // Set the cookie for login
                setcookie("userID", $user->id, time() + (86400 * 30), "/"); // Sets cookie for 30 days
                
                // Update the password to hashed version for future logins using Eloquent
                $user->password = password_hash($tryPass, PASSWORD_DEFAULT);
                $user->updated_at = date('Y-m-d H:i:s'); // Set current timestamp for updated_at
                $user->save();
                
                return $this->redirect('home.php');
            } else {
                // Password doesn't match, redirect to login page
                return $this->redirect('index.php');
            }
            
        } catch (Exception $e) {
            // Log the error
            error_log("Login error: " . $e->getMessage());
            
            // Redirect to login page with a generic error
            return $this->redirect('index.php', ['error' => 1]);
        }
    }
    
    /**
     * Handle user logout
     * 
     * @return void
     */
    public function logout()
    {
        // Clear session
        $_SESSION = array();
        
        // Clear cookie
        setcookie("userID", "", time() - 3600, "/");
        
        // Destroy session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Redirect to login page
        return $this->redirect('index.php');
    }
    
    /**
     * Update user profile
     * 
     * @return void
     */
    public function updateProfile()
    {
        // Require authentication
        $this->requireAuth();
        
        // Get current user
        $userData = $this->getAuthUser();
        $userId = $userData['id'];
        
        try {
            // Find the user by ID
            $user = User::find($userId);
            
            if (!$user) {
                return $this->redirect('/account', ['error' => 'user_not_found']);
            }
            
            // Update user fields
            if ($this->has('email')) {
                $user->email = $this->input('email');
            }
            
            if ($this->has('first_name')) {
                $user->first_name = $this->input('first_name');
            }
            
            if ($this->has('last_name')) {
                $user->last_name = $this->input('last_name');
            }
            
            if ($this->has('major')) {
                $user->major = $this->input('major');
            }
            
            if ($this->has('year')) {
                $user->year = $this->input('year');
            }
            
            if ($this->has('scholarship')) {
                $user->scholarship = $this->input('scholarship');
            }
            
            // Handle password update
            if ($this->has('password') && !empty($this->input('password'))) {
                $user->password = password_hash($this->input('password'), PASSWORD_DEFAULT);
            }
            
            // Validate and save
            if (!$user->validate()) {
                return $this->redirect('/account', ['error' => 'validation_failed']);
            }
            
            $user->save();
            
            // Redirect back to account page with success message
            return $this->redirect('/account', ['success' => 1]);
            
        } catch (Exception $e) {
            // Log error
            error_log("Update profile error: " . $e->getMessage());
            
            // Redirect with error
            return $this->redirect('/account', ['error' => 'system_error']);
        }
    }
}