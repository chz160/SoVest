<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use Exception;

/**
 * Auth Controller
 * 
 * Handles authentication and user registration.
 */
class AuthController extends Controller
{
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
        
        if ($credentials !== true) {
            //return $this->redirect('/login.php', ['error' => 'invalid_credentials']);
            return redirect()->route('/login.php', ['error' => 'invalid_credentials']);
            //header("Location: login.php");
        }
        
        try {
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
     
                return redirect()->intended('/home.php');
            }
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        } catch (Exception $e) {
            // Log the error
            error_log("Login error: " . $e->getMessage());
            
            // Redirect to login page with a generic error
            //return $this->redirect('/login.php', ['error' => 'system_error']);
            header("Location: login.php");
        }
    }
        
    /**
     * Handle registration form submission
     * 
     * @param Request $request
     * @return RedirectResponse
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
                //'password' => $validated['newPass'],
                'major' => $validated['newMajor'],
                'year' => $validated['newYear'],
                'scholarship' => $validated['newScholarship']
            ]);
            
            // Log the user in using Auth facade
            //Auth::login($user);
            
            // Redirect to login page with success message for web request
            return redirect("/login.php", ['success' => 1]);
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return redirect("/acctNew.php", ['error' => 'system_error']);
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