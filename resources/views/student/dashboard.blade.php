@extends('layouts.app')

@section('header', 'Student Dashboard')

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
                    <span>Enrollment, Demographics & Student Lifecycle Hub</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="users" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            Student Overview — {{ $school?->name ?? 'School Management' }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Managing student admissions, classroom allocation, gender demographics, promotions, and credentials.
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
                    <form method="GET" action="{{ route('sm-student.dashboard') }}" class="inline-block m-0">
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
                <a href="{{ route('sm-student.print-id-cards.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #FAF6F0; border: 1px solid #E7E0D8; color: #211B17;">
                    <i data-lucide="id-card" class="w-3.5 h-3.5" style="color: #B47A42;"></i>
                    <span>Print ID Cards</span>
                </a>

                <a href="{{ route('sm-student.students.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Add Admission</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $session && $globalCurrentSession->id !== $session->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived student records for <strong>{{ $session->name }}</strong>
            </span>
            <a href="{{ route('sm-student.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY STUDENT KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Active Enrollment Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Active Students</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $activePercentage }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $activeStudentsCount }} of {{ $totalStudents }}
                        </span>
                        <span class="text-stone-400 font-medium">Enrolled</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activePercentage }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $activePercentage }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Gender Parity / Female Share Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Gender Parity</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $girlsPercentage }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #FDF2F8; color: #DB2777;">
                            {{ $totalGirls }} F
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ $totalBoys }} M
                        </span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-pink-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $girlsPercentage }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $girlsPercentage }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Student Portal Readiness Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Portal Logins</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $loginCoverageRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ $portalUsersCount }} Accounts
                        </span>
                        <span class="text-stone-400 font-medium">Ready</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $loginCoverageRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $portalUsersCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Classroom Spread Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Active Classes</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $classCount }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $sectionCount }} Sections
                        </span>
                        <span class="text-stone-400 font-medium">Mapped</span>
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
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $classCount }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY STUDENT ANALYSIS CIRCLES (Class-wise Enrollment + Demographics Gauge) ── --}}
    <div class="dashboard-grid-2">

        {{-- Circle 1: Class-Wise Student Distribution (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            Classroom Distribution
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Class-Wise Student Enrollment</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $totalStudents }} Total Students
                    </span>
                </div>

                {{-- Interactive Donut Chart Container --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="classStudentsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($classStudentsData as $cItem)
                    <div class="p-2.5 rounded-xl border flex items-center justify-between"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $cItem['color'] }};"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-stone-800 block truncate">{{ $cItem['name'] }}</span>
                                <span class="text-[10px] text-stone-500 font-medium">{{ $cItem['percentage'] }}%</span>
                            </div>
                        </div>
                        <span class="text-sm font-black text-stone-900 ml-1">{{ $cItem['count'] }}</span>
                    </div>
                    @empty
                    <div class="col-span-3 text-xs text-stone-400 py-3 text-center">No students registered in this session</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Students enrolled across all academic grades</span>
                <a href="{{ route('sm-student.students.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all students</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        {{-- Circle 2: Student Demographics & Gender Health (Radial Gauge) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-pink-600">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                            Demographics Health
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Gender & Enrollment Balance</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $activeStudentsCount }} Active
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="studentDemographicsRadialChart" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Demographic Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Active</span>
                        <span class="text-base font-black text-emerald-600 block mt-0.5">{{ $activePercentage }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $activeStudentsCount }} of {{ $totalStudents }}</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Boys</span>
                        <span class="text-base font-black text-blue-600 block mt-0.5">{{ $boysPercentage }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $totalBoys }} Enrolled</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Girls</span>
                        <span class="text-base font-black text-pink-600 block mt-0.5">{{ $girlsPercentage }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $totalGirls }} Enrolled</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Session-wide gender and retention breakdown</span>
                <a href="{{ route('sm-student.students.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>Manage admissions</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SECONDARY STUDENT ANALYSIS CIRCLES (3-COLUMN GRID) ── --}}
    <div class="dashboard-grid-3">

        {{-- Circle 3A: Section-Wise Student Allocation (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                            Section Spread
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Section Allocation</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $sectionCount }} Sections
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="sectionStudentsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                    @forelse($sectionStudentsData as $sItem)
                    <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg border border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $sItem['color'] }};"></span>
                            <span class="font-bold text-stone-800 truncate max-w-[120px]">{{ $sItem['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-stone-900">{{ $sItem['count'] }}</span>
                            <span class="text-[10px] text-stone-400">({{ $sItem['percentage'] }}%)</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 text-center py-4">No sections allocated</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Section capacity ratio</span>
                <a href="{{ route('sm-student.students.index') }}" class="font-bold underline" style="color: #B47A42;">View Sections</a>
            </div>
        </div>

        {{-- Circle 3B: Student Progression & Lifecycle Health (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            Student Lifecycle
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Enrollment & Status</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $activePercentage }}% Active
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="studentStatusDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Status Breakdown Stats --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-emerald-50/70 border border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-emerald-900">Active Students</span>
                        </div>
                        <span class="font-black text-emerald-900">{{ $activeStudentsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-blue-50/70 border border-blue-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="font-bold text-blue-900">Promoted</span>
                        </div>
                        <span class="font-black text-blue-900">{{ $promotedCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-amber-50/70 border border-amber-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-amber-900">Transferred Out</span>
                        </div>
                        <span class="font-black text-amber-900">{{ $transferredCount }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Progression scale</span>
                <a href="{{ route('sm-student.promote.index') }}" class="font-bold underline" style="color: #B47A42;">Promote</a>
            </div>
        </div>

        {{-- Circle 3C: Student Portal Accounts & Credentials (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600">
                            <i data-lucide="key" class="w-4 h-4"></i>
                            Student Portal
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">User Account Credentials</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #F3E8FF; color: #7E22CE;">
                        {{ $portalUsersCount }} Logins
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="studentLoginsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Stats breakdown --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-purple-50/70 border border-purple-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <span class="font-bold text-purple-900">Accounts Active</span>
                        </div>
                        <span class="font-black text-purple-900">{{ $portalUsersCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-stone-50 border border-stone-200">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
                            <span class="font-bold text-stone-700">Pending Setup</span>
                        </div>
                        <span class="font-black text-stone-700">{{ $pendingLoginsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] px-2 text-stone-400">
                        <span>Portal Readiness Rate:</span>
                        <span class="font-bold text-stone-700">{{ $loginCoverageRate }}%</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Student login system</span>
                <a href="{{ route('sm-student.student-logins.index') }}" class="font-bold underline" style="color: #B47A42;">Manage Logins</a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: OPERATIONS & MANAGEMENT HUB (4 Quick Action Cards) ── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-stone-900">Student Operations Hub</h2>
                <p class="text-xs text-stone-500">Quick management shortcuts for admissions, ID card issuance, and promotions</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Student Directory --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #EFF6FF; color: #1D4ED8;">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalStudents }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Student Directory</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Browse all enrolled students, search profiles and edit details</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-student.students.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
                    <a href="{{ route('sm-student.students.create') }}" class="text-xs font-bold px-2 py-1 rounded-md text-white" style="background: #B47A42;">+ Add</a>
                </div>
            </div>

            {{-- 2. Print ID Cards --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFDF5; color: #047857;">
                        <i data-lucide="id-card" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $totalStudents }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Print ID Cards</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Generate and print high-resolution laminated student ID cards</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-student.print-id-cards.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Generate Cards</a>
                    <span class="text-[11px] font-semibold text-stone-400">Batch Print</span>
                </div>
            </div>

            {{-- 3. Student Promotion --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FFFBEB; color: #B45309;">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $promotedCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Promote Students</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Move students to the next session and class on passing grades</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-student.promote.index') }}" class="text-xs font-bold text-amber-700 hover:underline">Promote Wizard</a>
                    <span class="text-[11px] font-semibold text-stone-400">Next Session</span>
                </div>
            </div>

            {{-- 4. Transfer & Certificates --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #F3E8FF; color: #7E22CE;">
                        <i data-lucide="arrow-right-from-line" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $transferredCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Transfer Student</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Issue transfer certificates and manage departing student records</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('sm-student.transfer.index') }}" class="text-xs font-bold text-purple-700 hover:underline">Issue TC</a>
                    <span class="text-[11px] font-semibold text-stone-400">Certificates</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: RECENT ADMISSIONS & STUDENT DIRECTORY ── --}}
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden" style="border-color: #E7E0D8;">
        <div class="p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold text-stone-900">Recent Student Admissions</h3>
                <p class="text-xs text-stone-500">Newly registered students enrolled in {{ $session->name }}</p>
            </div>
            <a href="{{ route('sm-student.students.index') }}"
               class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border hover:bg-stone-50 transition"
               style="border-color: #DED5CB; color: #211B17;">
                <span>View Full Directory ({{ $totalStudents }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-stone-50/70 text-stone-600 font-bold uppercase tracking-wider text-[11px]" style="border-color: #F0EAE1;">
                        <th class="py-3 px-4">Adm No.</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4">Class & Section</th>
                        <th class="py-3 px-4 text-center">Gender</th>
                        <th class="py-3 px-4">Parent / Guardian</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-medium text-stone-800">
                    @forelse($recentStudents as $s)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-stone-600">
                            {{ $s->admission_no }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0"
                                     style="background: {{ strtolower((string)$s->gender) === 'female' ? '#EC4899' : '#3B82F6' }};">
                                    {{ strtoupper(substr($s->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-stone-900 block">{{ $s->full_name }}</span>
                                    <span class="text-[10px] text-stone-400">Roll: {{ $s->roll_number ?? '—' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold text-[11px]"
                                  style="background: #EFF6FF; color: #1D4ED8;">
                                {{ $s->schoolClass?->name ?? 'Unassigned' }}
                            </span>
                            @if($s->section)
                                <span class="text-stone-400 text-[11px]">({{ $s->section->name }})</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(strtolower((string)$s->gender) === 'female')
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-pink-100 text-pink-700">
                                    Female
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-blue-100 text-blue-700">
                                    Male
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-stone-800 block">{{ $s->father_name ?: ($s->guardian_name ?: '—') }}</span>
                            <span class="text-[10px] text-stone-400">{{ $s->father_phone ?: ($s->phone ?: '—') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(strtolower((string) $s->status) === 'active' || $s->status == '1')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                </span>
                            @elseif(strtolower((string) $s->status) === 'promoted')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-blue-100 text-blue-800">
                                    Promoted
                                </span>
                            @elseif(strtolower((string) $s->status) === 'transferred')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-amber-100 text-amber-800">
                                    Transferred
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-stone-100 text-stone-600">
                                    {{ $s->status ?: 'Inactive' }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('sm-student.students.edit', $s) }}"
                                   class="px-2 py-1 rounded text-[11px] font-bold border hover:bg-stone-100 text-stone-700"
                                   style="border-color: #DED5CB;">
                                    Edit
                                </a>
                                <a href="{{ route('sm-student.print-id-cards.print', $s) }}"
                                   target="_blank"
                                   class="px-2 py-1 rounded text-[11px] font-bold text-white hover:brightness-105"
                                   style="background: #B47A42;">
                                    ID Card
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-stone-400">
                            No students enrolled in this academic session yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── ApexCharts Script ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ── 1. Class-Wise Student Distribution Donut Chart ──
    const classChartEl = document.querySelector("#classStudentsDonutChart");
    if (classChartEl) {
        const classData = @json($classStudentsData);
        const series = classData.map(item => item.count);
        const labels = classData.map(item => item.name);
        const colors = classData.map(item => item.color);
        const totalStudents = {{ (int) $totalStudents }};

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
                                    label: 'Students',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
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
                            return val + ' Students';
                        }
                    }
                }
            };
            const chart = new ApexCharts(classChartEl, options);
            chart.render();
        } else {
            classChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No students enrolled</div>';
        }
    }

    // ── 2. Student Demographics Multi-Radial Bar ──
    const demographicsChartEl = document.querySelector("#studentDemographicsRadialChart");
    if (demographicsChartEl) {
        const activeRate = {{ (float) $activePercentage }};
        const boysRate = {{ (float) $boysPercentage }};
        const girlsRate = {{ (float) $girlsPercentage }};
        const totalStudents = {{ (int) $totalStudents }};

        const options = {
            series: [activeRate, boysRate, girlsRate],
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
                            label: 'Enrolled',
                            color: '#766C63',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: function (w) {
                                return totalStudents;
                            }
                        }
                    }
                }
            },
            colors: ['#10B981', '#3B82F6', '#EC4899'],
            labels: ['Active Rate', 'Boys Ratio', 'Girls Ratio'],
            stroke: { lineCap: 'round' }
        };
        const chart = new ApexCharts(demographicsChartEl, options);
        chart.render();
    }

    // ── 3. Section-Wise Student Allocation Donut Chart ──
    const sectionChartEl = document.querySelector("#sectionStudentsDonutChart");
    if (sectionChartEl) {
        const sectionData = @json($sectionStudentsData);
        const series = sectionData.map(item => item.count);
        const labels = sectionData.map(item => item.name);
        const colors = sectionData.map(item => item.color);
        const totalSections = {{ (int) $sectionCount }};

        if (series.length > 0 && series.reduce((a, b) => a + b, 0) > 0) {
            const options = {
                series: series,
                labels: labels,
                colors: colors.length > 0 ? colors : ['#3B82F6', '#10B981', '#8B5CF6'],
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
                                    label: 'Sections',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalSections;
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
            const chart = new ApexCharts(sectionChartEl, options);
            chart.render();
        } else {
            sectionChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No sections mapped</div>';
        }
    }

    // ── 4. Student Status Lifecycle Donut Chart ──
    const statusChartEl = document.querySelector("#studentStatusDonutChart");
    if (statusChartEl) {
        const activeCount = {{ (int) $activeStudentsCount }};
        const promotedCount = {{ (int) $promotedCount }};
        const transferredCount = {{ (int) $transferredCount }};
        const total = {{ (int) $totalStudents }};

        const hasStudents = total > 0;
        const series = hasStudents ? [activeCount, promotedCount, transferredCount] : [1];
        const labels = hasStudents ? ['Active', 'Promoted', 'Transferred'] : ['No Students'];
        const colors = hasStudents ? ['#10B981', '#3B82F6', '#F59E0B'] : ['#E5E7EB'];

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
                                label: hasStudents ? 'Active Rate' : 'Status',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return hasStudents ? '{{ $activePercentage }}%' : '0';
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
                        return hasStudents ? val + ' Students' : '0';
                    }
                }
            }
        };
        const chart = new ApexCharts(statusChartEl, options);
        chart.render();
    }

    // ── 5. Student Logins Share Donut Chart ──
    const loginsChartEl = document.querySelector("#studentLoginsDonutChart");
    if (loginsChartEl) {
        const portalUsers = {{ (int) $portalUsersCount }};
        const pendingUsers = {{ (int) $pendingLoginsCount }};
        const total = {{ (int) $totalStudents }};
        const coverageRate = {{ (float) $loginCoverageRate }};

        const hasStudents = total > 0;
        const series = hasStudents ? [portalUsers, pendingUsers] : [1];
        const labels = hasStudents ? ['Active Logins', 'Pending Setup'] : ['No Students'];
        const colors = hasStudents ? ['#8B5CF6', '#E5E7EB'] : ['#E5E7EB'];

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
                                label: 'Readiness',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return coverageRate + '%';
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
                        return hasStudents ? val + ' Students' : '0';
                    }
                }
            }
        };
        const chart = new ApexCharts(loginsChartEl, options);
        chart.render();
    }

});
</script>
@endsection
