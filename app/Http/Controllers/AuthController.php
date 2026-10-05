<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (session('auth_guard')) {
            return $this->redirectByGuard(session('auth_guard'));
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('login'));
        $password = $request->input('password');

        // 1. Check Super Admin (from config/env)
        if ($login === config('auth.super_admin_username') &&
            Hash::check($password, config('auth.super_admin_password_hash'))) {
            session([
                'auth_guard' => 'super_admin',
                'auth_user_id' => 0,
                'super_admin_data' => ['name' => 'Super Admin', 'role' => 'super_admin'],
            ]);

            return redirect()->route('school-management.dashboard');
        }

        // 2. Check School User (Admin / Principal)
        $schoolUser = SchoolUser::where('username', $login)
            ->orWhere('login_email', $login)
            ->orWhere('login_phone', $login)
            ->first();

        if ($schoolUser && Hash::check($password, $schoolUser->password)) {
            if (! $schoolUser->isActive()) {
                return back()->with('error', 'You are inactive for this session in this school. Contact to superadmin.');
            }

            Auth::guard('school_user')->login($schoolUser);

            session([
                'auth_guard' => 'school_user',
                'auth_user_id' => $schoolUser->id,
                'selected_school_id' => $schoolUser->school_id,
            ]);

            return redirect()->route('sm-administrator.dashboard');
        }

        // 3. Check Staff
        $staff = Staff::where('username', $login)
            ->orWhere('login_email', $login)
            ->orWhere('login_phone', $login)
            ->first();

        if ($staff && Hash::check($password, $staff->password)) {
            if (! $staff->isActive()) {
                return back()->with('error', 'You are inactive for this session in this school. Contact to superadmin.');
            }

            Auth::guard('staff')->login($staff);

            session([
                'auth_guard' => 'staff',
                'auth_user_id' => $staff->id,
                'selected_school_id' => $staff->school_id,
            ]);

            return redirect()->route('staff.dashboard');
        }

        return back()->withInput($request->only('login'))
            ->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->flush();

        return redirect()->route('login');
    }

    private function redirectByGuard(string $guard): RedirectResponse
    {
        return match ($guard) {
            'super_admin' => redirect()->route('school-management.dashboard'),
            'school_user' => redirect()->route('sm-administrator.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
