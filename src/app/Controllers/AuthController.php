<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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
    public function __construct(?AuthServiceInterface $authService, array $services = [])
    {
        parent::__construct($authService, null, $services);
        
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
    /**
     * Display registration form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function registerForm(Request $request)
    {
        // If user is already logged in, redirect to home
        if (Auth::check()) {
            return redirect()->route('user.home');
        }
        
        // Set page title
        $pageTitle = 'Create Account';
        
        // Get error message if any
        $error = $request->input('error');
        
        // Return the registration view
        return view('pages.user.register', [
            'pageTitle' => $pageTitle,
            'error' => $error
        ]);
    }
    
    /**
     * Handle registration form submission
     * 
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the input with Laravel's validation system
        $validated = $request->validate([
            'newEmail' => ['required', 'email', 'unique:users,email'],
            'newPass' => ['required', 'min:8'],
            'newMajor' => ['required'],
            'newYear' => ['required'],
            'newScholarship' => ['required']
        ]);
        
        try {
            // Create user directly using Laravel's User model
            $user = User::create([
                'email' => $validated['newEmail'],
                'password' => Hash::make($validated['newPass']),
                'major' => $validated['newMajor'],
                'year' => $validated['newYear'],
                'scholarship' => $validated['newScholarship']
            ]);
            
            // Log the user in using Auth facade
            Auth::login($user);
            
            // Different response for API request
            if ($this->isApiRequest()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful',
                    'data' => ['user_id' => $user->id],
                    'redirect' => route('login.form')
                ]);
            }
            
            // Redirect to login page with success message for web request
            return redirect()->route('login.form')->with('success', 1);
            
        } catch (Exception $e) {
            // Log the error
            error_log("Registration error: " . $e->getMessage());
            
            if ($this->isApiRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'System error occurred',
                    'errors' => []
                ], 500);
            }
            
            // Redirect to registration page with error
            return redirect()->route('register.form')->with('error', 'system_error');
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