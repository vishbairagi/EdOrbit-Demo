<?php

namespace App\Http\Controllers;

use App\Models\SchoolUser;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmUsersController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('selected_school_id');
        $search = $request->input('search');

        $admins = SchoolUser::where('school_id', $schoolId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('login_email', 'like', "%{$search}%"))
            ->get()
            ->map(fn ($u) => array_merge($u->toArray(), ['user_type' => 'Admin/Principal']));

        $staffList = Staff::with('role')
            ->where('school_id', $schoolId)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('login_email', 'like', "%{$search}%"))
            ->get()
            ->map(fn ($s) => array_merge($s->toArray(), ['user_type' => $s->role?->name ?? 'Staff']));

        $users = collect($admins)->merge($staffList)->sortBy('name');

        return view('sm-administrator.users.index', compact('users', 'search'));
    }
}
