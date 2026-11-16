@extends('layouts.app')

@section('header', 'School Dashboard')

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
                    <span>Campus Management & Operations</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="school" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            {{ $school->name }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Reg: {{ $school->registration_number ?? 'REG-N/A' }} • {{ $school->email ?? 'No email configured' }}
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
                    <form method="GET" action="{{ route('sm-school.dashboard') }}" class="inline-block m-0">
                        <select name="session_id" onchange="this.form.submit()"
                                class="text-xs font-bold rounded-lg py-1 px-2.5 bg-white cursor-pointer focus:outline-none focus:ring-1"
                                style="border: 1px solid #DED5CB; color: #211B17;">
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ ($currentSession && $currentSession->id == $session->id) ? 'selected' : '' }}>
                                    {{ $session->name }}{{ ($globalCurrentSession && $globalCurrentSession->id == $session->id) ? ' ★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Action Buttons --}}
                <a href="{{ route('sm-school.classes.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #171411; color: #ffffff;">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Class</span>
                </a>

                <a href="{{ route('sm-school.class-groups.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Manage Sections</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $currentSession && $globalCurrentSession->id !== $currentSession->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived session data for <strong>{{ $currentSession->name }}</strong>
            </span>
            <a href="{{ route('sm-school.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Student Enrollment Rate Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Student Enrollment</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ number_format($totalStudents) }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ number_format($totalActiveStudents) }} Active
                        </span>
                        @if($totalInactiveStudents > 0)
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #FEF3C7; color: #B45309;">
                            {{ number_format($totalInactiveStudents) }} Inactive
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeStudentsPercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $activeStudentsPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Staff Capacity Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Staff Members</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ number_format($totalStaff) }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F0FDF4; color: #15803D;">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ number_format($activeStaff) }} Active
                        </span>
                        @if($inactiveStaff > 0)
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #FFF1F2; color: #BE123C;">
                            {{ number_format($inactiveStaff) }} Inactive
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeStaffPercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $activeStaffPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Fee Collection Health Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Collection Rate</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $collectionRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="font-bold text-emerald-700">
                            {{ $currencySymbol }}{{ number_format($feesCollected, 0) }} Collected
                        </span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="transition-all duration-1000 ease-out" style="color: #B47A42;" stroke-dasharray="{{ $collectionRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $collectionRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Classes & Academic Sections Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Academic Classes</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $totalClasses }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $totalSections }} Sections
                        </span>
                        <span class="text-stone-400 font-medium">Active</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-purple-600 transition-all duration-1000 ease-out" stroke-dasharray="85, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $totalClasses }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY ANALYSIS CIRCLES (Active/Inactive Student + Total Income & Pending) ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 dashboard-grid-2">

        {{-- Circle 1: Active / Inactive Student Circle (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Student Enrollment Analysis
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Active & Inactive Students</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $activeStudentsPercent }}% Active
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[240px]">
                    <div id="schoolStudentStatusDonut" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         style="background: #FAFDFB; border-color: #D1FAE5;">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-emerald-950 block">Active Students</span>
                                <span class="text-[11px] text-emerald-600 font-semibold">{{ $activeStudentsPercent }}% of total</span>
                            </div>
                        </div>
                        <span class="text-base font-black text-emerald-800">{{ number_format($totalActiveStudents) }}</span>
                    </div>

                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         style="background: #FFFDF9; border-color: #FEF3C7;">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0"></span>
                            <div>
                                <span class="text-xs font-bold text-amber-950 block">Inactive / On Leave</span>
                                <span class="text-[11px] text-amber-600 font-semibold">{{ 100 - $activeStudentsPercent }}% of total</span>
                            </div>
                        </div>
                        <span class="text-base font-black text-amber-800">{{ number_format($totalInactiveStudents) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1; color: #766C63;">
                <span>Total Enrolled: <strong style="color: #211B17;">{{ number_format($totalStudents) }} Students</strong></span>
                <span class="flex items-center gap-2">
                    <span class="text-blue-600 font-medium">Male: {{ $maleStudents }}</span> • 
                    <span class="text-pink-600 font-medium">Female: {{ $femaleStudents }}</span>
                </span>
            </div>
        </div>

        {{-- Circle 2: Total Income & Pending Fees Circle (Radial Gauge & Accounting) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider" style="color: #B47A42;">
                            <i data-lucide="circle-dollar-sign" class="w-4 h-4"></i>
                            Accounting & Revenue Health
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Total Income & Pending Dues</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #FFFBEB; color: #B45309;">
                        {{ $pendingRate }}% Pending
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[240px]">
                    <div id="schoolIncomePendingRadial" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Financial Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500 block">Total Income</span>
                        <span class="text-xs font-black block mt-0.5" style="color: #211B17;">
                            {{ $currencySymbol }}{{ number_format($totalIncome, 0) }}
                        </span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: #ECFDF5; border-color: #A7F3D0;">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Collected</span>
                        <span class="text-xs font-black text-emerald-800 block mt-0.5">
                            {{ $currencySymbol }}{{ number_format($feesCollected, 0) }}
                        </span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: #FFF1F2; border-color: #FECDD3;">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Pending Dues</span>
                        <span class="text-xs font-black text-rose-800 block mt-0.5">
                            {{ $currencySymbol }}{{ number_format($pendingDues, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px]"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $paidInvoicesCount }} Paid
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px]"
                          style="background: #FEF3C7; color: #B45309;">
                        {{ $partialInvoicesCount }} Partial
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px]"
                          style="background: #FFF1F2; color: #BE123C;">
                        {{ $rawUnpaidCount }} Unpaid
                    </span>
                </div>
                <span class="text-stone-400 text-[11px] font-medium">{{ $unpaidInvoices }} Outstanding</span>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: ADDITIONAL ANALYSIS CIRCLES (Staff Roles + Class Distribution) ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Circle 3A: Staff by Role & Status Circle --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3 border-b" style="border-color: #F0EAE1;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600"
                             style="background: #EFF6FF;">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #211B17;">Staff Roles</h3>
                            <span class="text-[11px] text-stone-400">Department Distribution</span>
                        </div>
                    </div>
                    <span class="text-xs font-black px-2 py-0.5 rounded-full"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $totalStaff }} Staff
                    </span>
                </div>

                {{-- Chart --}}
                <div id="staffRolesChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Roles list --}}
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    @forelse($staffByRole as $roleName => $count)
                    <div class="p-2 rounded-lg border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">{{ $roleName }}</span>
                        <span class="font-black" style="color: #B47A42;">{{ $count }}</span>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 py-4 text-center">No staff records found</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex justify-between" style="border-color: #F0EAE1;">
                <span>Active Staff: <strong class="text-emerald-600">{{ $activeStaff }}</strong></span>
                <span>Active Rate: <strong class="text-blue-600">{{ $activeStaffPercent }}%</strong></span>
            </div>
        </div>

        {{-- Circle 3B: Class-wise Student Load Distribution Circle --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3 border-b" style="border-color: #F0EAE1;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-purple-600"
                             style="background: #FAF5FF;">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #211B17;">Class Distribution</h3>
                            <span class="text-[11px] text-stone-400">Students per Grade</span>
                        </div>
                    </div>
                    <span class="text-xs font-black px-2 py-0.5 rounded-full"
                          style="background: #FAF5FF; color: #7E22CE;">
                        {{ $totalClasses }} Classes
                    </span>
                </div>

                {{-- Chart --}}
                <div id="classDistributionChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Classes List --}}
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    @forelse($classesWithStudents as $cls)
                    <div class="p-2 rounded-lg border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">{{ $cls->name }}</span>
                        <span class="font-black text-purple-700">{{ $cls->students_count }} Students</span>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 py-4 text-center">No classes registered</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex justify-between" style="border-color: #F0EAE1;">
                <span>Total Sections: <strong class="text-purple-600">{{ $totalSections }}</strong></span>
                <span>Grades: <strong style="color: #211B17;">{{ $totalClasses }}</strong></span>
            </div>
        </div>

        {{-- Circle 3C: Leaves & Attendance Processing Circle --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3 border-b" style="border-color: #F0EAE1;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-amber-600"
                             style="background: #FFFBEB;">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #211B17;">Leaves Processing</h3>
                            <span class="text-[11px] text-stone-400">Approval Health</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-wrap">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;" title="Approved">
                            {{ $totalApprovedLeaves }} Appr
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #FFFBEB; color: #B45309;" title="Pending">
                            {{ $totalPendingLeaves }} Pend
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #FFF1F2; color: #BE123C;" title="Rejected">
                            {{ $totalRejectedLeaves }} Rej
                        </span>
                    </div>
                </div>

                {{-- Chart --}}
                <div id="leavesStatusChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Highlights --}}
                <div class="space-y-2 pt-2">
                    <div class="p-2.5 rounded-xl border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div>
                            <span class="font-bold text-stone-800 block">Student Leaves</span>
                            <span class="text-[10px] text-stone-400">{{ $approvedStudentLeaves + $pendingStudentLeaves + $rejectedStudentLeaves }} Total</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-right font-bold text-[11px]">
                            <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">{{ $approvedStudentLeaves }} Appr</span>
                            <span class="text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">{{ $pendingStudentLeaves }} Pend</span>
                            <span class="text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">{{ $rejectedStudentLeaves }} Rej</span>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div>
                            <span class="font-bold text-stone-800 block">Staff Leaves</span>
                            <span class="text-[10px] text-stone-400">{{ $approvedStaffLeaves + $pendingStaffLeaves + $rejectedStaffLeaves }} Total</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-right font-bold text-[11px]">
                            <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded">{{ $approvedStaffLeaves }} Appr</span>
                            <span class="text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">{{ $pendingStaffLeaves }} Pend</span>
                            <span class="text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded">{{ $rejectedStaffLeaves }} Rej</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex items-center justify-between" style="border-color: #F0EAE1;">
                <span>Total Requests: <strong style="color: #211B17;">{{ $totalAllLeaves }}</strong></span>
                <span class="flex items-center gap-1.5 text-xs">
                    <span class="text-emerald-600 font-bold">{{ $totalApprovedLeaves }} Approved</span> • 
                    <span class="text-amber-600 font-bold">{{ $totalPendingLeaves }} Pending</span> • 
                    <span class="text-rose-600 font-bold">{{ $totalRejectedLeaves }} Rejected</span>
                </span>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: STAFF ROSTER WITH USERNAMES TABLE ── --}}
    <div id="staffDirectory" class="bg-white rounded-2xl border shadow-sm overflow-hidden scroll-mt-6"
         style="border-color: #E7E0D8;">
        <div class="p-5 border-b flex flex-col md:flex-row md:items-center justify-between gap-4"
             style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2" style="color: #211B17;">
                    <i data-lucide="users" class="w-4 h-4" style="color: #B47A42;"></i>
                    Staff Directory & Username Credentials
                </h3>
                <p class="text-xs text-stone-500 mt-0.5">
                    Complete listing of faculty, administrative staff, usernames, roles, and status.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Staff Search Bar --}}
                <form method="GET" action="{{ route('sm-school.dashboard') }}" class="flex items-center gap-2 m-0">
                    @if(request('session_id'))
                        <input type="hidden" name="session_id" value="{{ request('session_id') }}">
                    @endif
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                        <input type="text" name="staff_search" value="{{ request('staff_search') }}"
                               placeholder="Search name, username, role..."
                               class="text-xs pl-8 pr-3 py-1.5 rounded-xl bg-white border border-stone-200 focus:outline-none focus:ring-1 focus:ring-[#B47A42] w-52 sm:w-64"
                               style="color: #211B17;">
                    </div>
                    @if(request('staff_search'))
                        <a href="{{ route('sm-school.dashboard', array_filter(['session_id' => request('session_id')])) }}#staffDirectory"
                           class="text-xs font-semibold px-2 py-1 rounded-lg hover:bg-stone-100" style="color: #B47A42;">
                            Clear
                        </a>
                    @endif
                </form>

                <span class="text-xs font-bold px-2.5 py-1.5 rounded-xl border flex items-center gap-1.5"
                      style="background: #FAF6F0; border-color: #E7E0D8; color: #B47A42;">
                    <span>{{ $allStaff->total() }} Total Staff</span>
                    @if($allStaff->total() > 0)
                        <span class="text-[10px] text-stone-400 font-normal">({{ $allStaff->firstItem() }}-{{ $allStaff->lastItem() }})</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-stone-500 uppercase tracking-wider text-[11px] border-b"
                        style="background: #FAF6F0; border-color: #F0EAE1;">
                        <th class="py-3 px-4 font-bold">Staff Member</th>
                        <th class="py-3 px-4 font-bold">Username / Login ID</th>
                        <th class="py-3 px-4 font-bold">Role / Designation</th>
                        <th class="py-3 px-4 font-bold">Employee ID</th>
                        <th class="py-3 px-4 font-bold">Contact</th>
                        <th class="py-3 px-4 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: #F0EAE1;">
                    @forelse($allStaff as $staff)
                    @php
                        $stActive = strtolower((string)$staff->status) === 'active' || $staff->status == '1';
                        $initials = collect(explode(' ', $staff->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                    @endphp
                    <tr class="hover:bg-stone-50/70 transition-colors">
                        <td class="py-3 px-4 font-bold">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0"
                                     style="background: #FAF6F0; color: #B47A42; border: 1px solid #DED5CB;">
                                    {{ $initials ?: 'ST' }}
                                </div>
                                <div>
                                    <div class="font-bold text-stone-900">{{ $staff->name }}</div>
                                    <div class="text-[11px] text-stone-400">{{ $staff->gender ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1 font-mono text-xs font-bold px-2 py-0.5 rounded-md"
                                  style="background: #F4F1EA; color: #211B17; border: 1px solid #E7E0D8;">
                                <i data-lucide="at-sign" class="w-3 h-3 text-stone-400"></i>
                                {{ $staff->username ?: ($staff->login_email ?: ($staff->email ?: 'EMP-' . $staff->employee_id)) }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  style="background: rgba(180, 122, 66, 0.1); color: #B47A42;">
                                {{ $staff->role?->name ?: ($staff->designation ?: 'Staff Member') }}
                            </span>
                        </td>

                        <td class="py-3 px-4 font-mono font-semibold text-stone-600">
                            #{{ $staff->employee_id ?: 'N/A' }}
                        </td>

                        <td class="py-3 px-4 text-stone-600">
                            <div>{{ $staff->phone ?: 'No phone' }}</div>
                            <div class="text-[11px] text-stone-400">{{ $staff->email ?: 'No email' }}</div>
                        </td>

                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                  style="background: {{ $stActive ? '#ECFDF5' : '#FFF1F2' }}; color: {{ $stActive ? '#047857' : '#BE123C' }};">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $stActive ? '#10B981' : '#F43F5E' }};"></span>
                                {{ $stActive ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-400 text-xs">
                            No staff records found{{ request('staff_search') ? ' matching "'.request('staff_search').'"' : ' for this campus' }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($allStaff->hasPages())
        <div class="px-5 py-3.5 border-t flex items-center justify-between"
             style="border-color: #F0EAE1; background: #FFFCF7;">
            <div class="text-xs text-stone-500 font-medium hidden sm:block">
                Showing <strong>{{ $allStaff->firstItem() }}</strong> to <strong>{{ $allStaff->lastItem() }}</strong> of <strong>{{ $allStaff->total() }}</strong> staff members
            </div>
            <div>
                {{ $allStaff->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- ── SECTION 4: OPERATIONAL TILES (EXPENSES, LIBRARY, INVOICES) ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <div class="bg-white rounded-xl p-4 border shadow-sm flex items-center justify-between"
             style="border-color: #E7E0D8;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-blue-600" style="background: #EFF6FF;">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-stone-500 uppercase block">Total Expenses</span>
                    <span class="text-sm font-black block" style="color: #211B17;">
                        {{ $currencySymbol }}{{ number_format($totalExpenses, 0) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm flex items-center justify-between"
             style="border-color: #E7E0D8;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-emerald-600" style="background: #ECFDF5;">
                    <i data-lucide="book" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-stone-500 uppercase block">Total Books</span>
                    <span class="text-sm font-black block" style="color: #211B17;">
                        {{ number_format($totalBooks) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm flex items-center justify-between"
             style="border-color: #E7E0D8;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-amber-600" style="background: #FFFBEB;">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-stone-500 uppercase block">Student Leaves</span>
                    <span class="text-sm font-black block" style="color: #211B17;">
                        {{ $pendingStudentLeaves }} Pending
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border shadow-sm flex items-center justify-between"
             style="border-color: #E7E0D8;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-rose-600" style="background: #FFF1F2;">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-stone-500 uppercase block">Staff Leaves</span>
                    <span class="text-sm font-black block" style="color: #211B17;">
                        {{ $pendingStaffLeaves }} Pending
                    </span>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ── ApexCharts Scripts for Interactive Circles ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // ── 1. Student Status Donut (Active vs Inactive) ──
    const studentStatusEl = document.querySelector("#schoolStudentStatusDonut");
    if (studentStatusEl) {
        const activeStudents = {{ (int) $totalActiveStudents }};
        const inactiveStudents = {{ (int) $totalInactiveStudents }};
        const totalStudents = {{ (int) $totalStudents }};

        const studentOptions = {
            series: [activeStudents, inactiveStudents],
            labels: ['Active Students', 'Inactive Students'],
            colors: ['#10B981', '#F59E0B'],
            chart: {
                type: 'donut',
                height: 240,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', fontWeight: 600, color: '#78716c', offsetY: -4 },
                            value: { show: true, fontSize: '20px', fontWeight: 800, color: '#1c1917', offsetY: 4 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Enrolled',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#a8a29e',
                                formatter: function () {
                                    return totalStudents;
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
                        const pct = totalStudents > 0 ? ((val / totalStudents) * 100).toFixed(1) : 0;
                        return val + ' (' + pct + '%)';
                    }
                }
            }
        };
        const studentChart = new ApexCharts(studentStatusEl, studentOptions);
        studentChart.render();
    }

    // ── 2. Total Income & Pending Fees Radial Bar Meter ──
    const incomeRadialEl = document.querySelector("#schoolIncomePendingRadial");
    if (incomeRadialEl) {
        const collectionRate = {{ (float) $collectionRate }};
        const totalIncome = {{ (float) $totalIncome }};
        const feesCollected = {{ (float) $feesCollected }};
        const currency = @json($currencySymbol);

        const incomeOptions = {
            series: [collectionRate],
            chart: {
                height: 240,
                type: 'radialBar',
                animations: { enabled: true, easing: 'easeinout', speed: 900 }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    hollow: {
                        margin: 0,
                        size: '70%',
                        background: '#ffffff',
                    },
                    track: {
                        background: '#f5f5f4',
                        strokeWidth: '100%',
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            color: '#78716c',
                            fontSize: '11px',
                            fontWeight: 700,
                            offsetY: -8,
                            text: 'COLLECTED'
                        },
                        value: {
                            show: true,
                            offsetY: 4,
                            color: '#1c1917',
                            fontSize: '24px',
                            fontWeight: 800,
                            formatter: function (val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'horizontal',
                    shadeIntensity: 0.5,
                    gradientToColors: ['#10B981'],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100],
                    colorStops: [
                        { offset: 0, color: '#B47A42', opacity: 1 },
                        { offset: 100, color: '#10B981', opacity: 1 }
                    ]
                }
            },
            stroke: { dashArray: 3 },
            labels: ['Collection Rate'],
            tooltip: {
                enabled: true,
                theme: 'dark',
                y: {
                    formatter: function () {
                        return currency + feesCollected.toLocaleString() + ' of ' + currency + totalIncome.toLocaleString();
                    }
                }
            }
        };
        const incomeChart = new ApexCharts(incomeRadialEl, incomeOptions);
        incomeChart.render();
    }

    // ── 3. Staff Roles Donut Chart ──
    const staffRolesEl = document.querySelector("#staffRolesChart");
    if (staffRolesEl) {
        const roleLabels = @json($staffByRole->keys()->values());
        const roleSeries = @json($staffByRole->values());
        const totalStaff = {{ (int) $totalStaff }};

        if (roleSeries.length > 0) {
            const staffOptions = {
                series: roleSeries,
                labels: roleLabels,
                colors: ['#3B82F6', '#B47A42', '#10B981', '#8B5CF6', '#F59E0B', '#EC4899'],
                chart: { type: 'donut', height: 180, animations: { enabled: true } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: { show: true, fontSize: '18px', fontWeight: 800, color: '#1c1917', offsetY: 6 },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Staff',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#78716c',
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
                            return val + ' Members';
                        }
                    }
                }
            };
            const staffChart = new ApexCharts(staffRolesEl, staffOptions);
            staffChart.render();
        } else {
            staffRolesEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No staff records</div>';
        }
    }

    // ── 4. Class Distribution Donut Chart ──
    const classDistEl = document.querySelector("#classDistributionChart");
    if (classDistEl) {
        const classLabels = @json($classesWithStudents->pluck('name')->values());
        const classSeries = @json($classesWithStudents->pluck('students_count')->values());
        const totalClasses = {{ (int) $totalClasses }};

        if (classSeries.length > 0 && classSeries.reduce((a, b) => a + b, 0) > 0) {
            const classOptions = {
                series: classSeries,
                labels: classLabels,
                colors: ['#8B5CF6', '#B47A42', '#3B82F6', '#10B981', '#EC4899', '#06B6D4'],
                chart: { type: 'donut', height: 180, animations: { enabled: true } },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: { show: true, fontSize: '18px', fontWeight: 800, color: '#1c1917', offsetY: 6 },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Grades',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#78716c',
                                    formatter: function () {
                                        return totalClasses;
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
                            return val + ' Students';
                        }
                    }
                }
            };
            const classChart = new ApexCharts(classDistEl, classOptions);
            classChart.render();
        } else {
            classDistEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No class enrollment data</div>';
        }
    }

    // ── 5. Leaves Approval Status Donut Chart ──
    const leavesEl = document.querySelector("#leavesStatusChart");
    if (leavesEl) {
        const approvedLeaves = {{ (int) $totalApprovedLeaves }};
        const pendingLeaves = {{ (int) $totalPendingLeaves }};
        const rejectedLeaves = {{ (int) $totalRejectedLeaves }};
        const totalLeaves = {{ (int) $totalAllLeaves }};

        const hasLeavesData = totalLeaves > 0;
        const leavesSeries = hasLeavesData ? [approvedLeaves, pendingLeaves, rejectedLeaves] : [1];
        const leavesLabels = hasLeavesData ? ['Approved', 'Pending', 'Rejected'] : ['No Leaves'];
        const leavesColors = hasLeavesData ? ['#10B981', '#F59E0B', '#EF4444'] : ['#E5E7EB'];

        const leavesOptions = {
            series: leavesSeries,
            labels: leavesLabels,
            colors: leavesColors,
            chart: { type: 'donut', height: 180, animations: { enabled: true } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: { show: true, fontSize: '18px', fontWeight: 800, color: '#1c1917', offsetY: 6 },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Leaves',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#78716c',
                                formatter: function () {
                                    return totalLeaves;
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
                        return hasLeavesData ? val + ' Requests' : '0';
                    }
                }
            }
        };
        const leavesChart = new ApexCharts(leavesEl, leavesOptions);
        leavesChart.render();
    }
});
</script>
@endsection
