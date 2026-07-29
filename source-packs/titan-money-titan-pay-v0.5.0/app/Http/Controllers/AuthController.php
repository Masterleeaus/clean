<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        $data['demoUsers'] = config('app.demo_mode', false)
            ? User::select('id', 'name', 'email')->take(3)->get()
            : collect();
        return view('login')->with($data);
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()->symbols()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        auth()->login($user);

        return redirect('/chat');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string'],
        ]);

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $redirectRoute = 'chat';
            if ((bool) Auth::user()->is_platform_admin) {
                $redirectRoute = 'dashboard';
            }

            return redirect()->route($redirectRoute);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
