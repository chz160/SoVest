<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Database\Models\User;
use Exception;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\ServiceFactory;

/**
 * Auth Controller
 * 
 * Handles authentication and user registration.
 */
class AuthController extends Controller
{
    /**
     * @var AuthServiceInterface Auth service instance
     */
    protected $authService;
    
    /**
     * Constructor
     * 
     * @param AuthServiceInterface|null $authService Authentication service (injected)
     * @param array $services Additional services to inject (optional)
     */
    public function __construct(AuthServiceInterface $authService = null, array $services = [])
    {
        parent::__construct($authService, $services);
        
        // Fallback to ServiceFactory for backward compatibility
        if ($this->authService === null) {
            $this->authService = ServiceFactory::createAuthService();
        }
    }
    
    /**
     * Display login form
     * 
     * @return void
     */
    public function loginForm()
    {
        // If user is already logged in, redirect to home
        if ($this->isAuthenticated()) {
            return $this->redirect('home');
        }
        
        // Set page title
        $pageTitle = 'Login';
        
        // Check for success message from registration
        $success = $this->input('success');
        
        // Render the login view
        return view('pages/user/login', [
            'pageTitle' => $pageTitle,
            'success' => $success
        ]);
    }
    
    /**
     * Handle login form submission
     * 
     * @return void
     */
    public function login(Request $request): RedirectResponse
    {
        // Validate the input
        $credentials = $request->validate([
            'tryEmail' => ['required', 'email'],
            'tryPass' => ['required'],
        ]);
        
        if ($validation !== true) {
            // If validation fails, redirect to login with errors
            if ($this->isApiRequest()) {
                $this->jsonError('Login validation failed', $validation);
            } else {
                return $this->redirect('login.php', ['error' => 'invalid_credentials']);
            }
        }
        
        try {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
     
                return redirect()->intended('home');
            }
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        } catch (Exception $e) {
            // Log the error
            error_log("Login error: " . $e->getMessage());
            
            if ($this->isApiRequest()) {
                $this->jsonError('System error occurred', [], 500);
            } else {
                // Redirect to login page with a generic error
                return $this->redirect('login', ['error' => 'system_error']);
            }
        }
    }
    
    /**
     * Display registration form
     * 
     * @return void
     */
    public function registerForm()
    {
        // If user is already logged in, redirect to home
        if ($this->isAuthenticated()) {
            return $this->redirect('home.php');
        }
        
        // Set page title
        $pageTitle = 'Create Account';
        
        // Get error message if any
        $error = $this->input('error');
        
        // Render the registration view
        $this->render('user/register', [
            'pageTitle' => $pageTitle,
            'error' => $error
        ]);
    }
    
    /**
     * Handle registration form submission
     * 
     * @return void
     */
    public function register()
    {
        // Validate the input
        $validation = $this->validateRequest([
            'newEmail' => 'required|email',
            'newPass' => 'required|min:8',
            'newMajor' => 'required',
            'newYear' => 'required',
            'newScholarship' => 'required'
        ]);
        
        if ($validation !== true) {
            if ($this->isApiRequest()) {
                $this->jsonError('Registration validation failed', $validation);
                return;
            }
            
            // For backward compatibility, convert validation errors to simple error codes
            if (isset($validation['newEmail'])) {
                return $this->redirect('/register', ['error' => 'invalid_email']);
            } else if (isset($validation['newPass'])) {
                return $this->redirect('/register', ['error' => 'password_too_short']);
            } else {
                return $this->redirect('/register', ['error' => 'validation_failed']);
            }
        }
        
        // Extract the form data
        $userData = [
            'email' => $this->input('newEmail', ''),
            'password' => $this->input('newPass', ''),
            'major' => $this->input('newMajor', ''),
            'year' => $this->input('newYear', ''),
            'scholarship' => $this->input('newScholarship', '')
        ];
        
        try {
            // Register user using AuthService
            $userId = $this->authService->register($userData);
            
            if ($userId) {
                if ($this->isApiRequest()) {
                    $this->jsonSuccess('Registration successful', ['user_id' => $userId], '/login');
                } else {
                    // Registration successful, redirect to login page with success message
                    return $this->redirect('/login', ['success' => 1]);
                }
            } else {
                // Registration failed, check user model for validation errors
                $user = new User();
                foreach ($userData as $key => $value) {
                    if (property_exists($user, $key)) {
                        $user->$key = $value;
                    }
                }
                $user->validate();
                $errors = $user->getErrors();
                
                if ($this->isApiRequest()) {
                    $this->jsonError('Registration failed', $errors);
                } else {
                    if (isset($errors['email'])) {
                        return $this->redirect('/register', ['error' => 'invalid_email']);
                    } elseif (isset($errors['password'])) {
                        return $this->redirect('/register', ['error' => 'password_too_short']);
                    } else {
                        // Generic error for other validation failures
                        return $this->redirect('/register', ['error' => 'validation_failed']);
                    }
                }
            }
        } catch (Exception $e) {
            // Log the error
            error_log("Registration error: " . $e->getMessage());
            
            if ($this->isApiRequest()) {
                $this->jsonError('System error occurred', [], 500);
            } else {
                // Redirect to registration page with error
                return $this->redirect('/register', ['error' => 'system_error']);
            }
        }
    }
    
    /**
     * Handle user logout
     * 
     * @return void
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}