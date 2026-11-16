@extends('layouts.app')

@section('header', 'School Management Dashboard')

@section('content')
<style>
.dashboard-grid-main {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 1.5rem !important;
}
@media (min-width: 1024px) {
    .dashboard-grid-main {
        grid-template-columns: 1.4fr 1fr !important;
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

    {{-- ── Top Hero / Welcome Banner ── --}}
    <div class="relative overflow-hidden rounded-2xl p-6 lg:p-8 shadow-xl"
         style="background: linear-gradient(135deg, #171411 0%, #231C17 50%, #36291F 100%) !important; border: 1px solid #3E2F23 !important; color: #ffffff !important; box-shadow: 0 10px 25px -5px rgba(23, 20, 17, 0.25);">
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full pointer-events-none" style="background: rgba(180, 122, 66, 0.15); filter: blur(48px);"></div>
        <div class="absolute right-32 -bottom-16 w-52 h-52 rounded-full pointer-events-none" style="background: rgba(59, 130, 246, 0.12); filter: blur(48px);"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold"
                     style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); color: #E8C99A;">
                    <span class="w-2 h-2 rounded-full animate-ping" style="background: #34D399;"></span>
                    <span>System Analytics & Monitoring Hub</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight" style="color: #FFFFFF !important;">
                    Multi-School Overview & Analysis
                </h1>
                <p class="text-xs lg:text-sm max-w-2xl" style="color: #D4C3B5 !important;">
                    Real-time visual distribution of students across affiliated schools, central accounting collection health, and active academic session status.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl px-4 py-2.5 flex items-center gap-3"
                     style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                         style="background: rgba(180, 122, 66, 0.25); border: 1px solid rgba(180, 122, 66, 0.4);">
                        <i data-lucide="calendar" class="w-4 h-4" style="color: #E8C99A;"></i>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider" style="color: #BFAEA1;">Active Session</div>
                        <div class="text-xs font-bold flex items-center gap-1.5" style="color: #FFFFFF;">
                            {{ $currentSession ? $currentSession->name : 'Not Selected' }}
                            <span class="inline-block w-2 h-2 rounded-full"
                                  style="background: {{ $currentSession && in_array(strtolower((string)$currentSession->status), ['active', '1']) ? '#34D399' : '#FBBF24' }};"></span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('school-management.schools.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl shadow-lg hover:brightness-110 transition-all duration-200"
                   style="background: linear-gradient(135deg, #B47A42, #966333); color: #ffffff !important; box-shadow: 0 8px 18px rgba(180, 122, 66, 0.35);">
                    <i data-lucide="plus-circle" class="w-4 h-4" style="color: #ffffff;"></i>
                    <span style="color: #ffffff !important;">Add New School</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 4 Key KPI Circular Metric Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Total Schools Card --}}
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Schools</span>
                    <h3 class="text-2xl font-black text-stone-900 mt-1">{{ $totalSchools }}</h3>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $activeSchools }} Active
                        </span>
                        @if($inactiveSchools > 0)
                        <span class="inline-flex items-center gap-1 text-rose-600 font-semibold bg-rose-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            {{ $inactiveSchools }} Inactive
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-600 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeSchoolsPercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black text-stone-800">{{ $activeSchoolsPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Total Students Card --}}
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Students</span>
                    <h3 class="text-2xl font-black text-stone-900 mt-1">{{ number_format($totalStudents) }}</h3>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ number_format($totalActiveStudents) }} Active
                        </span>
                        @if($totalInactiveStudents > 0)
                        <span class="inline-flex items-center gap-1 text-amber-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
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
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeStudentPercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black text-stone-800">{{ $activeStudentPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Accounting Collection Rate Card --}}
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Collection Health</span>
                    <h3 class="text-2xl font-black text-stone-900 mt-1">{{ $collectionRate }}%</h3>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="text-emerald-700 font-semibold">
                            Collected: {{ $currencySymbol }}{{ number_format($totalCollected, 0) }}
                        </span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-[#B47A42] transition-all duration-1000 ease-out" stroke-dasharray="{{ $collectionRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black text-stone-800">{{ $collectionRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Academic Sessions Card --}}
        <div class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Academic Sessions</span>
                    <h3 class="text-2xl font-black text-stone-900 mt-1">{{ $totalSessions }}</h3>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 text-purple-700 font-semibold bg-purple-50 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                            {{ $activeSessions }} Active
                        </span>
                        @if($inactiveSessions > 0)
                        <span class="inline-flex items-center gap-1 text-stone-600 font-medium bg-stone-100 px-2 py-0.5 rounded-full">
                            {{ $inactiveSessions }} Inactive
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-purple-600 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeSessionsPercent }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black text-stone-800">{{ $activeSessionsPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY ANALYSIS CIRCLES (Student Distribution + Accounting Circle) ── --}}
    <div class="dashboard-grid-main">

        {{-- Circle 1: School-Wise Student Distribution (Donut Chart) --}}
        <div class="w-full bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#B47A42]">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                            Student Share Analysis
                        </div>
                        <h2 class="text-lg font-bold text-stone-900 mt-0.5">Which School Has How Many Students</h2>
                    </div>
                    <span class="text-xs bg-stone-100 text-stone-700 px-2.5 py-1 rounded-full font-semibold">
                        {{ $schools->count() }} Schools Enrolled
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center mt-4">
                    {{-- Interactive Donut Chart Container --}}
                    <div class="md:col-span-7 flex flex-col items-center justify-center min-h-[280px]">
                        <div id="schoolStudentsDonutChart" class="w-full flex justify-center"></div>
                    </div>

                    {{-- School Legend & Counts List --}}
                    <div class="md:col-span-5 space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($schoolDistribution as $item)
                        <div class="p-2.5 rounded-xl border border-stone-100 hover:border-stone-200 bg-stone-50/60 hover:bg-stone-50 transition-all flex items-center justify-between">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                <div class="truncate">
                                    <div class="text-xs font-bold text-stone-800 truncate" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </div>
                                    <div class="text-[11px] text-stone-400">
                                        {{ $item['active_students_count'] }} Active
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <span class="text-xs font-black text-stone-900">{{ number_format($item['students_count']) }}</span>
                                <span class="text-[10px] text-stone-500 block font-semibold">{{ $item['percentage'] }}%</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-stone-400 text-xs">
                            No schools registered yet.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Footer Summary Pill --}}
            <div class="mt-4 pt-3 border-t border-stone-100 flex flex-wrap items-center justify-between text-xs text-stone-500">
                <span class="flex items-center gap-1 font-medium">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-[#B47A42]"></i>
                    Total Student Enrollment: <strong class="text-stone-800">{{ number_format($totalStudents) }}</strong>
                </span>
                <span class="text-emerald-600 font-semibold">
                    {{ $activeStudentPercent }}% Overall Active Rate
                </span>
            </div>
        </div>

        {{-- Circle 2: Accounting & Financial Health Circle (Radial / Donut) --}}
        <div class="w-full bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="circle-dollar-sign" class="w-4 h-4"></i>
                            Accounting Analysis
                        </div>
                        <h2 class="text-lg font-bold text-stone-900 mt-0.5">Fee Collection & Dues</h2>
                    </div>
                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full font-semibold">
                        {{ $collectionRate }}% Collected
                    </span>
                </div>

                {{-- Radial Gauge Chart --}}
                <div class="flex flex-col items-center justify-center mt-3">
                    <div id="accountingRadialChart" class="w-full flex justify-center min-h-[220px]"></div>
                </div>

                {{-- Financial Metric Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="bg-stone-50 rounded-xl p-2.5 text-center border border-stone-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500 block">Total Billed</span>
                        <span class="text-xs font-black text-stone-900 block mt-0.5">
                            {{ $currencySymbol }}{{ number_format($totalInvoiced, 0) }}
                        </span>
                    </div>

                    <div class="bg-emerald-50/70 rounded-xl p-2.5 text-center border border-emerald-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Collected</span>
                        <span class="text-xs font-black text-emerald-800 block mt-0.5">
                            {{ $currencySymbol }}{{ number_format($totalCollected, 0) }}
                        </span>
                    </div>

                    <div class="bg-rose-50/70 rounded-xl p-2.5 text-center border border-rose-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 block">Pending Dues</span>
                        <span class="text-xs font-black text-rose-800 block mt-0.5">
                            {{ $currencySymbol }}{{ number_format($totalPending, 0) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Invoice Status Distribution Badges --}}
            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-semibold text-[11px]">
                        {{ $paidInvoicesCount }} Paid
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold text-[11px]">
                        {{ $partialInvoicesCount }} Partial
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-semibold text-[11px]">
                        {{ $unpaidInvoicesCount }} Unpaid
                    </span>
                </div>
                <span class="text-stone-400 text-[11px] font-medium">{{ $totalInvoicesCount }} Invoices</span>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SESSIONS & SYSTEM ACTIVE / INACTIVE ANALYSIS CIRCLES ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 dashboard-grid-3">

        {{-- Circle 3A: Academic Sessions Status (Active vs Inactive) --}}
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-stone-900">Session Status</h3>
                            <span class="text-[11px] text-stone-400">Active vs Inactive of Sessions</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">
                        {{ $totalSessions }} Total
                    </span>
                </div>

                {{-- Chart --}}
                <div id="sessionStatusChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Sessions List --}}
                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                    @foreach($sessions as $ses)
                    @php
                        $isSesActive = in_array(strtolower((string)$ses->status), ['active', '1']);
                        $isCurrent = $currentSession && $currentSession->id === $ses->id;
                    @endphp
                    <div class="p-2 rounded-lg {{ $isCurrent ? 'bg-purple-50/70 border border-purple-200' : 'bg-stone-50 border border-stone-100' }} flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $isSesActive ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                            <span class="font-bold {{ $isCurrent ? 'text-purple-900' : 'text-stone-800' }}">{{ $ses->name }}</span>
                            @if($isCurrent)
                            <span class="text-[9px] uppercase font-bold bg-purple-200 text-purple-800 px-1.5 py-0.2 rounded">Current</span>
                            @endif
                        </div>
                        <span class="font-semibold text-[11px] {{ $isSesActive ? 'text-emerald-600' : 'text-stone-400' }}">
                            {{ $isSesActive ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-stone-100 text-[11px] text-stone-500 flex justify-between">
                <span>Active: <strong class="text-emerald-600">{{ $activeSessions }}</strong></span>
                <span>Inactive: <strong class="text-stone-600">{{ $inactiveSessions }}</strong></span>
            </div>
        </div>

        {{-- Circle 3B: School Operational Status (Active vs Inactive Schools) --}}
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i data-lucide="building" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-stone-900">School Status</h3>
                            <span class="text-[11px] text-stone-400">Operational Breakdown</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                        {{ $totalSchools }} Schools
                    </span>
                </div>

                {{-- Chart --}}
                <div id="schoolStatusChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Status Progress Bars --}}
                <div class="space-y-3 pt-2">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-stone-600 font-medium flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active Schools
                            </span>
                            <span class="font-bold text-stone-800">{{ $activeSchools }} ({{ $activeSchoolsPercent }}%)</span>
                        </div>
                        <div class="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-700" style="width: {{ $activeSchoolsPercent }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-stone-600 font-medium flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span> Inactive Schools
                            </span>
                            <span class="font-bold text-stone-800">{{ $inactiveSchools }} ({{ 100 - $activeSchoolsPercent }}%)</span>
                        </div>
                        <div class="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-rose-400 h-2 rounded-full transition-all duration-700" style="width: {{ 100 - $activeSchoolsPercent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-stone-100 text-[11px] text-stone-500 flex justify-between">
                <span>Active Rate: <strong class="text-emerald-600">{{ $activeSchoolsPercent }}%</strong></span>
                <span>Operational: <strong class="text-blue-600">{{ $activeSchools }} / {{ $totalSchools }}</strong></span>
            </div>
        </div>

        {{-- Circle 3C: Student Enrollment Health (Active vs Inactive Students) --}}
        <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-stone-900">Student Enrollment</h3>
                            <span class="text-[11px] text-stone-400">Active vs Inactive Status</span>
                        </div>
                    </div>
                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                        {{ number_format($totalStudents) }} Students
                    </span>
                </div>

                {{-- Chart --}}
                <div id="studentStatusChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Highlights --}}
                <div class="space-y-2 pt-2">
                    <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
                            <span class="text-xs font-bold text-emerald-900">Active Enrolled</span>
                        </div>
                        <span class="text-xs font-black text-emerald-800">{{ number_format($totalActiveStudents) }}</span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-stone-50 border border-stone-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user-x" class="w-4 h-4 text-stone-400"></i>
                            <span class="text-xs font-bold text-stone-700">Inactive / On Leave</span>
                        </div>
                        <span class="text-xs font-black text-stone-800">{{ number_format($totalInactiveStudents) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-stone-100 text-[11px] text-stone-500 flex justify-between">
                <span>Total Enrolled: <strong class="text-stone-800">{{ number_format($totalStudents) }}</strong></span>
                <span>Active Rate: <strong class="text-emerald-600">{{ $activeStudentPercent }}%</strong></span>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: DETAILED SCHOOL MATRIX TABLE WITH MINI CIRCLE CHARTS ── --}}
    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-[#B47A42]"></i>
                    Schools Comparative Matrix
                </h3>
                <p class="text-xs text-stone-500 mt-0.5">
                    Individual breakdown of student enrollment counts, fees collection, classes, and operational state.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-stone-500">Total Schools:</span>
                <span class="text-xs font-bold bg-[#B47A42]/10 text-[#B47A42] px-2 py-0.5 rounded-md">{{ $schools->count() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50/80 text-stone-500 uppercase tracking-wider text-[11px] border-b border-stone-100">
                        <th class="py-3 px-4 font-bold">School Name</th>
                        <th class="py-3 px-4 font-bold text-center">Status</th>
                        <th class="py-3 px-4 font-bold text-center">Student Share</th>
                        <th class="py-3 px-4 font-bold text-right">Students (Active/Total)</th>
                        <th class="py-3 px-4 font-bold text-right">Accounting (Collected/Billed)</th>
                        <th class="py-3 px-4 font-bold text-center">Collection %</th>
                        <th class="py-3 px-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($schoolDistribution as $item)
                    @php
                        $fin = collect($schoolFinancials)->firstWhere('school_id', $item['id']) ?? ['invoiced' => 0, 'collected' => 0, 'pending' => 0, 'rate' => 0];
                    @endphp
                    <tr class="hover:bg-stone-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-stone-900">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                <div>
                                    <div class="font-bold text-stone-900">{{ $item['name'] }}</div>
                                    <div class="text-[11px] text-stone-400 font-normal">
                                        {{ $item['classes_count'] }} Classes • {{ $item['staff_count'] }} Staff
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $item['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $item['is_active'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ ucfirst($item['status']) }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            {{-- Mini circle indicator --}}
                            <div class="inline-flex items-center gap-2">
                                <div class="relative w-8 h-8 flex items-center justify-center">
                                    <svg class="w-8 h-8 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-stone-100" stroke-width="4" stroke="currentColor" fill="none"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path style="color: {{ $item['color'] }};" stroke-dasharray="{{ $item['percentage'] }}, 100" stroke-width="4" stroke-linecap="round" stroke="currentColor" fill="none"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                </div>
                                <span class="font-bold text-stone-700 text-xs">{{ $item['percentage'] }}%</span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <div class="font-black text-stone-900">{{ number_format($item['students_count']) }}</div>
                            <div class="text-[11px] text-emerald-600 font-semibold">{{ number_format($item['active_students_count']) }} active</div>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <div class="font-bold text-emerald-700">
                                {{ $currencySymbol }}{{ number_format($fin['collected'], 0) }}
                            </div>
                            <div class="text-[11px] text-stone-400">
                                Total: {{ $currencySymbol }}{{ number_format($fin['invoiced'], 0) }}
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5 font-bold {{ $fin['rate'] >= 75 ? 'text-emerald-700' : ($fin['rate'] >= 40 ? 'text-amber-700' : 'text-rose-700') }}">
                                <span class="w-2 h-2 rounded-full {{ $fin['rate'] >= 75 ? 'bg-emerald-500' : ($fin['rate'] >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ $fin['rate'] }}%
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('school-management.schools.switch', $item['id']) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#171411] hover:bg-[#B47A42] text-white font-semibold rounded-lg text-xs shadow-sm transition-all duration-200">
                                <span>Switch</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-stone-400">No schools available to display.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── SECTION 4: RECENT SCHOOLS GRID (PRESERVED & ENHANCED) ── --}}
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-stone-900 flex items-center gap-2">
                <i data-lucide="building-2" class="w-5 h-5 text-[#B47A42]"></i>
                School Portals & Quick Access
            </h3>
            <a href="{{ route('school-management.schools.index') }}" class="text-xs text-[#B47A42] hover:underline font-bold flex items-center gap-1">
                View All Schools <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        @if($recentSchools->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($recentSchools as $school)
                @php
                    $scDist = $schoolDistribution->firstWhere('id', $school->id);
                    $sActive = in_array(strtolower((string)$school->status), ['active', '1']);
                @endphp
                <a href="{{ route('school-management.schools.switch', $school) }}" class="block bg-white rounded-2xl p-5 shadow-sm border border-stone-200 hover:border-[#B47A42]/50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-[#B47A42]/10 to-transparent rounded-bl-full pointer-events-none transition-transform group-hover:scale-125 duration-300"></div>

                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-xl bg-[#171411]/5 group-hover:bg-[#171411] flex items-center justify-center transition-colors duration-300">
                                <i data-lucide="school" class="w-6 h-6 text-[#B47A42] group-hover:text-[#e8c99a] transition-colors duration-300"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-stone-900 group-hover:text-[#B47A42] transition-colors duration-200">
                                    {{ $school->name }}
                                </h4>
                                <span class="text-[11px] text-stone-400">Reg: {{ $school->registration_number ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sActive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ $sActive ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    {{-- Quick Counters --}}
                    <div class="grid grid-cols-2 gap-2 mb-4 bg-stone-50 rounded-xl p-2.5 border border-stone-100 group-hover:bg-[#171411]/5 transition-colors">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-stone-400 block">Students</span>
                            <span class="text-sm font-black text-stone-900">
                                {{ $scDist ? number_format($scDist['students_count']) : 0 }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-stone-400 block">Student Share</span>
                            <span class="text-sm font-black text-[#B47A42]">
                                {{ $scDist ? $scDist['percentage'] : 0 }}%
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-xs text-stone-600">
                        <div class="flex items-center gap-2 truncate">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-stone-400 flex-shrink-0"></i>
                            <span class="truncate">{{ $school->phone ?? 'Phone not set' }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 truncate">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-stone-400 flex-shrink-0"></i>
                            <span class="truncate">{{ $school->email ?? 'Email not set' }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 truncate">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-stone-400 flex-shrink-0"></i>
                            <span class="truncate">{{ $school->address ?? 'Location not set' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between text-xs text-[#B47A42] font-bold group-hover:text-[#8f5e2f]">
                        <span>Enter Dashboard</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
            <i data-lucide="school" class="w-12 h-12 text-stone-300 mx-auto mb-3"></i>
            <p class="text-base font-bold text-stone-800">No schools created yet.</p>
            <p class="mt-1 text-xs text-stone-500">Get started by adding your first school campus.</p>
            <a href="{{ route('school-management.schools.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-[#171411] hover:bg-[#B47A42] text-white text-xs font-bold rounded-xl transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Add School
            </a>
        </div>
        @endif
    </div>

</div>

{{-- ── ApexCharts Script & Interactive Charts Initialization ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Re-trigger Lucide icons in case dynamic elements loaded
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Chart Palette & Data
    const schoolLabels = @json($schoolDistribution->pluck('name')->values());
    const schoolSeries = @json($schoolDistribution->pluck('students_count')->values());
    const schoolColors = @json($schoolDistribution->pluck('color')->values());
    const totalStudentsCount = {{ (int) $totalStudents }};

    // ── 1. School Students Distribution Donut Chart ──
    const studentDonutEl = document.querySelector("#schoolStudentsDonutChart");
    if (studentDonutEl) {
        if (schoolSeries.length > 0 && totalStudentsCount > 0) {
            const studentDonutOptions = {
                series: schoolSeries,
                labels: schoolLabels,
                colors: schoolColors,
                chart: {
                    type: 'donut',
                    height: 280,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    },
                    toolbar: { show: false }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '13px',
                                    fontWeight: 600,
                                    color: '#78716c',
                                    offsetY: -5
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 800,
                                    color: '#1c1917',
                                    offsetY: 5,
                                    formatter: function (val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total Students',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#a8a29e',
                                    formatter: function (w) {
                                        return totalStudentsCount;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    colors: ['#ffffff']
                },
                legend: {
                    show: false
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            const pct = totalStudentsCount > 0 ? ((val / totalStudentsCount) * 100).toFixed(1) : 0;
                            return val + " Students (" + pct + "%)";
                        }
                    }
                }
            };
            const schoolChart = new ApexCharts(studentDonutEl, studentDonutOptions);
            schoolChart.render();
        } else {
            studentDonutEl.innerHTML = '<div class="text-xs text-stone-400 py-10 text-center">No student enrollment data available</div>';
        }
    }

    // ── 2. Accounting Radial Gauge Chart ──
    const accountingRadialEl = document.querySelector("#accountingRadialChart");
    if (accountingRadialEl) {
        const collectionRate = {{ (float) $collectionRate }};
        const totalInvoiced = {{ (float) $totalInvoiced }};
        const totalCollected = {{ (float) $totalCollected }};
        const currency = @json($currencySymbol);

        const accountingOptions = {
            series: [collectionRate],
            chart: {
                height: 220,
                type: 'radialBar',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 900
                }
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
            stroke: {
                dashArray: 3
            },
            labels: ['Collection Rate'],
            tooltip: {
                enabled: true,
                theme: 'dark',
                y: {
                    formatter: function () {
                        return currency + totalCollected.toLocaleString() + ' of ' + currency + totalInvoiced.toLocaleString();
                    }
                }
            }
        };
        const accountingChart = new ApexCharts(accountingRadialEl, accountingOptions);
        accountingChart.render();
    }

    // ── 3. Academic Sessions Status (Active vs Inactive) Donut Chart ──
    const sessionStatusEl = document.querySelector("#sessionStatusChart");
    if (sessionStatusEl) {
        const activeSessions = {{ (int) $activeSessions }};
        const inactiveSessions = {{ (int) $inactiveSessions }};
        
        const sessionOptions = {
            series: [activeSessions, inactiveSessions],
            labels: ['Active Sessions', 'Inactive Sessions'],
            colors: ['#10B981', '#94A3B8'],
            chart: {
                type: 'donut',
                height: 180,
                animations: { enabled: true }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 800,
                                color: '#1c1917',
                                offsetY: 6
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Sessions',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#78716c',
                                formatter: function () {
                                    return activeSessions + '/' + (activeSessions + inactiveSessions);
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
                        return val + ' Sessions';
                    }
                }
            }
        };
        const sessionChart = new ApexCharts(sessionStatusEl, sessionOptions);
        sessionChart.render();
    }

    // ── 4. School Operational Status Donut Chart ──
    const schoolStatusEl = document.querySelector("#schoolStatusChart");
    if (schoolStatusEl) {
        const activeSchools = {{ (int) $activeSchools }};
        const inactiveSchools = {{ (int) $inactiveSchools }};

        const schoolStatusOptions = {
            series: [activeSchools, inactiveSchools],
            labels: ['Active Schools', 'Inactive Schools'],
            colors: ['#10B981', '#F43F5E'],
            chart: {
                type: 'donut',
                height: 180,
                animations: { enabled: true }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 800,
                                color: '#1c1917',
                                offsetY: 6
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Schools',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#78716c',
                                formatter: function () {
                                    return activeSchools + '/' + (activeSchools + inactiveSchools);
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
                        return val + ' Schools';
                    }
                }
            }
        };
        const schoolStatusChart = new ApexCharts(schoolStatusEl, schoolStatusOptions);
        schoolStatusChart.render();
    }

    // ── 5. Student Enrollment Status Donut Chart ──
    const studentStatusEl = document.querySelector("#studentStatusChart");
    if (studentStatusEl) {
        const activeStudents = {{ (int) $totalActiveStudents }};
        const inactiveStudents = {{ (int) $totalInactiveStudents }};

        const studentStatusOptions = {
            series: [activeStudents, inactiveStudents],
            labels: ['Active Students', 'Inactive Students'],
            colors: ['#3B82F6', '#CBD5E1'],
            chart: {
                type: 'donut',
                height: 180,
                animations: { enabled: true }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: false },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 800,
                                color: '#1c1917',
                                offsetY: 6
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Students',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#78716c',
                                formatter: function () {
                                    return activeStudents;
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
        const studentStatusChart = new ApexCharts(studentStatusEl, studentStatusOptions);
        studentStatusChart.render();
    }
});
</script>
@endsection
