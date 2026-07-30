<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\CompanyRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        $data['demoUsers'] = User::select('id', 'name', 'email')->take(3)->get();
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
            'password' => 'required|min:6|confirmed',
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $company = Company::create([
                'public_id' => (string) Str::uuid(),
                'name' => $validated['name'].' Workspace',
                'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(8)),
                'status' => 'active',
                'timezone' => config('app.timezone', 'Australia/Melbourne'),
                'currency' => 'AUD',
                'country_code' => 'AU',
                'created_by_user_id' => $user->id,
            ]);

            $ownerRole = CompanyRole::create([
                'company_id' => $company->id,
                'name' => 'Owner',
                'key' => 'owner',
                'description' => 'Full company ownership and administrative access.',
                'is_system' => true,
            ]);

            CompanyMembership::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role_id' => $ownerRole->id,
                'role_key' => 'owner',
                'status' => 'active',
                'is_owner' => true,
                'joined_at' => now(),
            ]);

            $user->forceFill(['active_company_id' => $company->id])->save();

            return $user;
        });

        auth()->login($user);

        return redirect('/chat');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $redirectRoute = 'chat';
            if (Auth::user()->email === config('app.admin_email')) {
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
