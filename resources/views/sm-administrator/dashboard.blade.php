@extends('layouts.app')

@section('header', 'Administrator Dashboard')

@section('content')
<style>
.dashboard-grid-2 {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
}
@media (min-width: 1024px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr 1fr !important;
    }
}
.dashboard-grid-3 {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
}
@media (min-width: 768px) {
    .dashboard-grid-3 {
        grid-template-columns: 1fr 1fr !important;
    }
}
@media (min-width: 1024px) {
    .dashboard-grid-3 {
        grid-template-columns: 1fr 1fr 1fr !important;
    }
}
</style>

<div class="space-y-6 pb-12">

    {{-- ── Top School Header & Session Switcher Banner ── --}}
    <div class="rounded-2xl p-6 lg:p-7 shadow-sm border transition-all"
         style="background: #ffffff; border-color: #E7E0D8; box-shadow: 0 1px 3px rgba(33,27,23,0.05);">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold"
                     style="background: rgba(180, 122, 66, 0.1); color: #B47A42; border: 1px solid rgba(180, 122, 66, 0.2);">
                    <span class="w-2 h-2 rounded-full" style="background: #10B981;"></span>
                    <span>Governance, Institutional Roles & Personnel Operations</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="shield-check" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            Administrator Overview — {{ $school?->name ?? 'Institutional Management' }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Managing administrative access, staff roles, leave processing, user accounts, and governance.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Session Selector Form --}}
                <div class="rounded-xl px-3.5 py-2 flex items-center gap-2.5"
                     style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                    <i data-lucide="calendar" class="w-4 h-4" style="color: #B47A42;"></i>
                    <span class="text-xs font-bold" style="color: #766C63;">Session:</span>
                    <form method="GET" action="{{ route('sm-administrator.dashboard') }}" class="inline-block m-0">
                        <select name="session_id" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-lg py-1 px-2.5 bg-white cursor-pointer focus:outline-none focus:ring-1"
                                style="border: 1px solid #DED5CB; color: #211B17;">
                            @foreach($sessions as $ses)
                                <option value="{{ $ses->id }}" {{ ($session && $session->id == $ses->id) ? 'selected' : '' }}>
                                    {{ $ses->name }}{{ ($globalCurrentSession && $globalCurrentSession->id == $ses->id) ? ' ★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Quick Buttons --}}
                <a href="{{ route('sm-administrator.roles.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #FAF6F0; border: 1px solid #E7E0D8; color: #211B17;">
                    <i data-lucide="shield" class="w-3.5 h-3.5" style="color: #B47A42;"></i>
                    <span>Manage Roles</span>
                </a>

                <a href="{{ route('sm-administrator.admins.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Add Admin</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $session && $globalCurrentSession->id !== $session->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived records for <strong>{{ $session->name }}</strong>
            </span>
            <a href="{{ route('sm-administrator.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY ADMINISTRATOR KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Active Staff Personnel Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Active Personnel</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $activeStaffRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $activeStaffCount }} of {{ $totalStaff }}
                        </span>
                        <span class="text-stone-400 font-medium">Active Staff</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeStaffRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $activeStaffRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Institutional Governance & Admins Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Governance Scale</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $adminsCount }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $rolesCount }} Roles
                        </span>
                        <span class="text-stone-400 font-medium">Configured</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-purple-600 transition-all duration-1000 ease-out" stroke-dasharray="100, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $adminsCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Staff Leaves Resolution Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Leaves Resolution</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $leavesApprovalRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: rgba(180, 122, 66, 0.1); color: #B47A42;">
                            {{ $approvedLeaves }} Approved
                        </span>
                        <span class="text-stone-400 font-medium">{{ $pendingLeaves }} Pending</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="transition-all duration-1000 ease-out" style="color: #B47A42;" stroke-dasharray="{{ $leavesApprovalRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $leavesApprovalRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. User Accounts & Credentials Coverage Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Credential Security</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $credentialCoverageRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ $usersWithLoginCount }} Users
                        </span>
                        <span class="text-stone-400 font-medium">Logins Active</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $credentialCoverageRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $totalUsers }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY ADMINISTRATOR ANALYSIS CIRCLES (Staff Roles + Governance Radial) ── --}}
    <div class="dashboard-grid-2">

        {{-- Circle 1: Staff Roles & Department Distribution (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Personnel Structure
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Staff Roles & Department Allocation</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $totalStaff }} Total Staff
                    </span>
                </div>

                {{-- Interactive Donut Chart Container --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="staffRolesDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($staffRolesData as $rItem)
                    <div class="p-2.5 rounded-xl border flex items-center justify-between"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $rItem['color'] }};"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-stone-800 block truncate">{{ $rItem['name'] }}</span>
                                <span class="text-[10px] text-stone-500 font-medium">{{ $rItem['percentage'] }}%</span>
                            </div>
                        </div>
                        <span class="text-sm font-black text-stone-900 ml-1">{{ $rItem['count'] }}</span>
                    </div>
                    @empty
                    <div class="col-span-3 text-xs text-stone-400 py-3 text-center">No staff members assigned</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Departmental staff allocation across the institution</span>
                <a href="{{ route('sm-administrator.staff.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all staff</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        {{-- Circle 2: Institutional Governance & Lifecycle Health (Radial Gauge) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            Operations Health
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Governance & Operations Pipeline</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #F3E8FF; color: #7E22CE;">
                        {{ $totalUsers }} Total Users
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="adminGovernanceRadialChart" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Governance Stat Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Staff Active</span>
                        <span class="text-base font-black text-emerald-600 block mt-0.5">{{ $activeStaffRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $activeStaffCount }} / {{ $totalStaff }}</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Leave Resol.</span>
                        <span class="text-base font-black block mt-0.5" style="color: #B47A42;">{{ $leavesApprovalRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $approvedLeaves }} of {{ $totalLeaves }}</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Logins</span>
                        <span class="text-base font-black text-blue-600 block mt-0.5">{{ $credentialCoverageRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $usersWithLoginCount }} Accounts</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Administrative accountability and account coverage</span>
                <a href="{{ route('sm-administrator.users.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all users</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SECONDARY ADMINISTRATOR ANALYSIS CIRCLES (3-COLUMN GRID) ── --}}
    <div class="dashboard-grid-3">

        {{-- Circle 3A: User Type Allocation (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-indigo-600">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            User Hierarchy
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">User Types Allocation</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #EEF2FF; color: #4338CA;">
                        {{ $totalUsers }} Users
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="userTypeDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Legend & Stats --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-indigo-50/70 border border-indigo-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <span class="font-bold text-indigo-900">Admins & Leadership</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-indigo-900">{{ $adminsCount }}</span>
                            <span class="text-[10px] text-indigo-600">({{ $adminUsersPct }}%)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-amber-50/70 border border-amber-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                            <span class="font-bold text-amber-900">Teaching & Staff</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-amber-900">{{ $totalStaff }}</span>
                            <span class="text-[10px] text-amber-600">({{ $staffUsersPct }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Institutional personnel mix</span>
                <a href="{{ route('sm-administrator.users.index') }}" class="font-bold underline" style="color: #B47A42;">Manage Users</a>
            </div>
        </div>

        {{-- Circle 3B: Staff Leave Resolution Pipeline (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-rose-600">
                            <i data-lucide="calendar-off" class="w-4 h-4"></i>
                            Attendance & Leaves
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Leaves Pipeline Health</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #FFE4E6; color: #E11D48;">
                        {{ $totalLeaves }} Requests
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="leavesResolutionDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Status Breakdown Stats --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-emerald-50/70 border border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-emerald-900">Approved</span>
                        </div>
                        <span class="font-black text-emerald-900">{{ $approvedLeaves }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-amber-50/70 border border-amber-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-amber-900">Pending Review</span>
                        </div>
                        <span class="font-black text-amber-900">{{ $pendingLeaves }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-blue-50/70 border border-blue-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="font-bold text-blue-900">Processing</span>
                        </div>
                        <span class="font-black text-blue-900">{{ $processingLeaves }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-1.5 px-2 rounded-lg bg-rose-50/70 border border-rose-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="font-bold text-rose-900">Rejected</span>
                        </div>
                        <span class="font-black text-rose-900">{{ $rejectedLeaves }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Staff leave processing</span>
                <a href="{{ route('sm-administrator.leaves.index') }}" class="font-bold underline" style="color: #B47A42;">Review Leaves</a>
            </div>
        </div>

        {{-- Circle 3C: Administrative Roles Allocation (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            Admin Roles
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Leadership Roles Share</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #F3E8FF; color: #7E22CE;">
                        {{ $adminsCount }} Admins
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="adminRolesDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                    @forelse($adminRolesData as $aItem)
                    <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg border border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $aItem['color'] }};"></span>
                            <span class="font-bold text-stone-800 truncate max-w-[120px]">{{ $aItem['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-stone-900">{{ $aItem['count'] }}</span>
                            <span class="text-[10px] text-stone-400">({{ $aItem['percentage'] }}%)</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 text-center py-4">No admins configured</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Executive authorities</span>
                <a href="{{ route('sm-administrator.admins.index') }}" class="font-bold underline" style="color: #B47A42;">Manage Admins</a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: OPERATIONS & MANAGEMENT HUB (8 Quick Action Cards) ── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-stone-900">Administrator Operations Hub</h2>
                <p class="text-xs text-stone-500">Quick management shortcuts for institutional personnel, roles, payroll, and security</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Admins Management --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #EFF6FF; color: #1D4ED8;">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $adminsCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Manage Admins</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Executive principals, headmasters, and system admins</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.admins.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View Admins</a>
                    <a href="{{ route('sm-administrator.admins.create') }}" class="text-xs font-bold px-2 py-1 rounded-md text-white" style="background: #B47A42;">+ Add</a>
                </div>
            </div>

            {{-- 2. Roles & Permissions --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #F3E8FF; color: #7E22CE;">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $rolesCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Roles & Permissions</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Configure role capabilities, ACL rules, and staff permissions</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.roles.index') }}" class="text-xs font-bold text-purple-700 hover:underline">Configure Roles</a>
                    <span class="text-[11px] font-semibold text-stone-400">Security</span>
                </div>
            </div>

            {{-- 3. Staff Directory --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFDF5; color: #047857;">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalStaff }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Staff Personnel</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Faculty directory, teachers, accountants, and support staff</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.staff.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Staff Directory</a>
                    <span class="text-[11px] font-semibold text-stone-400">{{ $activeStaffCount }} Active</span>
                </div>
            </div>

            {{-- 4. Staff Leaves --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FFE4E6; color: #E11D48;">
                        <i data-lucide="calendar-off" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalLeaves }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Leave Management</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Review, approve, or reject employee leave requests</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.leaves.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Review Leaves</a>
                    <span class="text-[11px] font-semibold text-stone-400">{{ $pendingLeaves }} Pending</span>
                </div>
            </div>

            {{-- 5. Staff ID Cards --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFEFF; color: #0891B2;">
                        <i data-lucide="id-card" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalStaff }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Staff ID Cards</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Design and print employee identification and smart badges</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.id-cards.index') }}" class="text-xs font-bold text-cyan-700 hover:underline">Print ID Cards</a>
                    <span class="text-[11px] font-semibold text-stone-400">Batch Print</span>
                </div>
            </div>

            {{-- 6. Staff Payroll --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFDF5; color: #059669;">
                        <i data-lucide="circle-dollar-sign" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $payrollRecordsCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Staff Payroll</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Calculate monthly salaries, allowances, and print payslips</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.payroll.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Manage Payroll</a>
                    <span class="text-[11px] font-semibold text-stone-400">Monthly</span>
                </div>
            </div>

            {{-- 7. System Users --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FFFBEB; color: #B45309;">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalUsers }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">System Users</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Directory of all school users with portal logins and credentials</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.users.index') }}" class="text-xs font-bold text-amber-700 hover:underline">View All Users</a>
                    <span class="text-[11px] font-semibold text-stone-400">All Logins</span>
                </div>
            </div>

            {{-- 8. Staff Attendance --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FAF6F0; color: #B47A42;">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $activeStaffCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Staff Attendance</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Track daily biometric or manual attendance and timesheets</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-administrator.attendance.index') }}" class="text-xs font-bold hover:underline" style="color: #B47A42;">Mark Attendance</a>
                    <span class="text-[11px] font-semibold text-stone-400">Daily Log</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: KEY ADMINISTRATORS & PERSONNEL DIRECTORY ── --}}
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden" style="border-color: #E7E0D8;">
        <div class="p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold text-stone-900">Active Administrative Staff & Personnel</h3>
                <p class="text-xs text-stone-500">Overview of administrators and staff members assigned to {{ $school?->name ?? 'School' }}</p>
            </div>
            <a href="{{ route('sm-administrator.users.index') }}"
               class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border hover:bg-stone-50 transition"
               style="border-color: #DED5CB; color: #211B17;">
                <span>View Full User Directory ({{ $totalUsers }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-stone-50/70 text-stone-600 font-bold uppercase tracking-wider text-[11px]" style="border-color: #F0EAE1;">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Role / Classification</th>
                        <th class="py-3 px-4">Username & Email</th>
                        <th class="py-3 px-4">Phone / Contact</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-medium text-stone-800">
                    {{-- Admins --}}
                    @foreach($recentAdmins as $adm)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="py-3.5 px-4 font-bold text-stone-900">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0"
                                     style="background: #8B5CF6;">
                                    {{ strtoupper(substr($adm->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-stone-900 block">{{ $adm->name }}</span>
                                    <span class="text-[10px] text-stone-400">Emp ID: {{ $adm->employee_id ?: 'ADM-' . $adm->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold text-[11px]"
                                  style="background: #F3E8FF; color: #7E22CE;">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                {{ $adm->role ?: 'Administrator' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-stone-800 block">{{ $adm->username ?: '—' }}</span>
                            <span class="text-[10px] text-stone-400">{{ $adm->login_email ?: ($adm->email ?: '—') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-stone-700">
                            {{ $adm->phone ?: '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('sm-administrator.admins.edit', $adm) }}"
                               class="px-2.5 py-1 rounded text-[11px] font-bold border hover:bg-stone-100 text-stone-700"
                               style="border-color: #DED5CB;">
                                Edit Admin
                            </a>
                        </td>
                    </tr>
                    @endforeach

                    {{-- Staff --}}
                    @foreach($recentStaff as $stf)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="py-3.5 px-4 font-bold text-stone-900">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0"
                                     style="background: #B47A42;">
                                    {{ strtoupper(substr($stf->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-stone-900 block">{{ $stf->name }}</span>
                                    <span class="text-[10px] text-stone-400">Staff #{{ $stf->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold text-[11px]"
                                  style="background: #EFF6FF; color: #1D4ED8;">
                                {{ $stf->role?->name ?? 'Staff' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-stone-800 block">{{ $stf->username ?: '—' }}</span>
                            <span class="text-[10px] text-stone-400">{{ $stf->login_email ?: ($stf->email ?: '—') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-stone-700">
                            {{ $stf->phone ?: '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(strtolower((string)$stf->status) === 'active' || $stf->status == '1')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-stone-100 text-stone-600">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('sm-administrator.staff.edit', $stf) }}"
                               class="px-2.5 py-1 rounded text-[11px] font-bold border hover:bg-stone-100 text-stone-700"
                               style="border-color: #DED5CB;">
                                Edit Staff
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── ApexCharts Script ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── 1. Staff Roles Distribution Donut Chart ──
    const rolesChartEl = document.querySelector("#staffRolesDonutChart");
    if (rolesChartEl) {
        const rolesData = @json($staffRolesData);
        const series = rolesData.map(item => item.count);
        const labels = rolesData.map(item => item.name);
        const colors = rolesData.map(item => item.color);
        const totalStaff = {{ (int) $totalStaff }};

        if (series.length > 0 && series.reduce((a, b) => a + b, 0) > 0) {
            const options = {
                series: series,
                labels: labels,
                colors: colors.length > 0 ? colors : ['#B47A42', '#3B82F6', '#10B981', '#8B5CF6'],
                chart: { type: 'donut', height: 230, animations: { enabled: true } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: { show: true, fontSize: '20px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Staff',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalStaff;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { width: 2, colors: ['#ffffff'] },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val + ' Personnel';
                        }
                    }
                }
            };
            const chart = new ApexCharts(rolesChartEl, options);
            chart.render();
        } else {
            rolesChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No staff assigned</div>';
        }
    }

    // ── 2. Governance Operations Multi-Radial Bar ──
    const governanceChartEl = document.querySelector("#adminGovernanceRadialChart");
    if (governanceChartEl) {
        const staffRate = {{ (float) $activeStaffRate }};
        const leaveRate = {{ (float) $leavesApprovalRate }};
        const loginRate = {{ (float) $credentialCoverageRate }};
        const totalUsers = {{ (int) $totalUsers }};

        const options = {
            series: [staffRate, leaveRate, loginRate],
            chart: {
                height: 230,
                type: 'radialBar',
                animations: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    hollow: { size: '32%' },
                    track: { background: '#F5F2EC', strokeWidth: '100%' },
                    dataLabels: {
                        name: { fontSize: '11px', fontWeight: 600, color: '#766C63', offsetY: -6 },
                        value: { fontSize: '16px', fontWeight: 800, color: '#211B17', offsetY: 4, formatter: val => val + '%' },
                        total: {
                            show: true,
                            label: 'Total Users',
                            color: '#766C63',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: function (w) {
                                return totalUsers;
                            }
                        }
                    }
                }
            },
            colors: ['#10B981', '#B47A42', '#3B82F6'],
            labels: ['Staff Active', 'Leave Resol.', 'Credentials'],
            stroke: { lineCap: 'round' }
        };
        const chart = new ApexCharts(governanceChartEl, options);
        chart.render();
    }

    // ── 3. User Type Allocation Donut Chart ──
    const userTypeChartEl = document.querySelector("#userTypeDonutChart");
    if (userTypeChartEl) {
        const admins = {{ (int) $adminsCount }};
        const staff = {{ (int) $totalStaff }};
        const total = {{ (int) $totalUsers }};

        const hasUsers = total > 0;
        const series = hasUsers ? [admins, staff] : [1];
        const labels = hasUsers ? ['Admins & Leadership', 'Teaching & Staff'] : ['No Users'];
        const colors = hasUsers ? ['#6366F1', '#F59E0B'] : ['#E5E7EB'];

        const options = {
            series: series,
            labels: labels,
            colors: colors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: true, fontSize: '18px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Users',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return total;
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return hasUsers ? val + ' Accounts' : '0';
                    }
                }
            }
        };
        const chart = new ApexCharts(userTypeChartEl, options);
        chart.render();
    }

    // ── 4. Staff Leaves Resolution Donut Chart ──
    const leavesChartEl = document.querySelector("#leavesResolutionDonutChart");
    if (leavesChartEl) {
        const approved = {{ (int) $approvedLeaves }};
        const pending = {{ (int) $pendingLeaves }};
        const processing = {{ (int) $processingLeaves }};
        const rejected = {{ (int) $rejectedLeaves }};
        const totalLeaves = {{ (int) $totalLeaves }};
        const approvalRate = {{ (float) $leavesApprovalRate }};

        const hasLeaves = totalLeaves > 0;
        const series = hasLeaves ? [approved, pending, processing, rejected] : [1];
        const labels = hasLeaves ? ['Approved', 'Pending', 'Processing', 'Rejected'] : ['No Leaves'];
        const colors = hasLeaves ? ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'] : ['#E5E7EB'];

        const options = {
            series: series,
            labels: labels,
            colors: colors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: true, fontSize: '18px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: hasLeaves ? 'Approval' : 'Status',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return hasLeaves ? approvalRate + '%' : '0';
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        return hasLeaves ? val + ' Requests' : 'No data';
                    }
                }
            }
        };
        const chart = new ApexCharts(leavesChartEl, options);
        chart.render();
    }

    // ── 5. Administrative Roles Donut Chart ──
    const adminRolesChartEl = document.querySelector("#adminRolesDonutChart");
    if (adminRolesChartEl) {
        const adminData = @json($adminRolesData);
        const series = adminData.map(item => item.count);
        const labels = adminData.map(item => item.name);
        const colors = adminData.map(item => item.color);
        const totalAdmins = {{ (int) $adminsCount }};

        if (series.length > 0 && series.reduce((a, b) => a + b, 0) > 0) {
            const options = {
                series: series,
                labels: labels,
                colors: colors.length > 0 ? colors : ['#8B5CF6', '#B47A42', '#3B82F6'],
                chart: { type: 'donut', height: 180, animations: { enabled: true } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: { show: true, fontSize: '18px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Admins',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalAdmins;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { width: 2, colors: ['#ffffff'] },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val + ' Admins';
                        }
                    }
                }
            };
            const chart = new ApexCharts(adminRolesChartEl, options);
            chart.render();
        } else {
            adminRolesChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No admins registered</div>';
        }
    }

});
</script>
@endsection
