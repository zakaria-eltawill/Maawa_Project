<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // First, check if user exists and is admin
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->with('error', __('auth.invalid_credentials'))
                ->withInput($request->only('email'));
        }

        // Check if user is admin
        if ($user->role !== 'admin') {
            return back()->with('error', __('auth.invalid_credentials'))
                ->withInput($request->only('email'));
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->with('error', __('auth.inactive'))
                ->withInput($request->only('email'));
        }

        \Log::info('Admin login attempt', ['email' => $credentials['email']]);

        // Attempt login with email and password
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            \Log::info('Admin login success', ['email' => $credentials['email']]);
            // Regenerate session ID to prevent session fixation
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        \Log::warning('Admin login failed', ['email' => $credentials['email']]);

        // Invalid password
        return back()->with('error', __('auth.invalid_credentials'))
            ->withInput($request->only('email'));
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
