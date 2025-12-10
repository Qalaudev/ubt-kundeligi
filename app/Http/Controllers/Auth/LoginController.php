<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Use web guard explicitly
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $user = Auth::guard('web')->user();
            
            // Check if user is banned
            if ($user->isBanned()) {
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => ['Your account has been banned. Please contact the administrator.'],
                ]);
            }
            
            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();
            
            // Save session explicitly
            $request->session()->save();
            
            // Create API token for the user
            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Store token in session for frontend access
            $request->session()->put('api_token', $token);

            // Redirect based on user role
            if ($user->isAdmin()) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        
        // Revoke all API tokens
        if ($user) {
            $user->tokens()->delete();
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
