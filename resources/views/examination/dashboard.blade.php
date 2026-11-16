@extends('layouts.app')

@section('header', 'Examination Dashboard')

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
                    <span>Assessments, Schedules & Marksheet Hub</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="award" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            Examination Overview — {{ $school->name }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Monitor exam timetables, admit cards issuance, grading criteria, and marksheet generation.
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
                    <form method="GET" action="{{ route('examination.dashboard') }}" class="inline-block m-0">
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
                <a href="{{ route('examination.groups.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #FAF6F0; border: 1px solid #E7E0D8; color: #211B17;">
                    <i data-lucide="layers" class="w-3.5 h-3.5" style="color: #B47A42;"></i>
                    <span>Exam Groups</span>
                </a>

                <a href="{{ route('examination.exams.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Create Exam</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $session && $globalCurrentSession->id !== $session->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived examination data for <strong>{{ $session->name }}</strong>
            </span>
            <a href="{{ route('examination.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY EXAMINATION KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Active Exams Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Active Exams</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $activeExamsRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $activeExamsCount }} of {{ $examCount }}
                        </span>
                        <span class="text-stone-400 font-medium">Active</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $activeExamsRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $activeExamsRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Admit Card Coverage Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Admit Cards Coverage</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $admitCardCoverageRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ $admitCardCount }} Issued
                        </span>
                        <span class="text-stone-400 font-medium">Cards</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $admitCardCoverageRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $admitCardCoverageRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Results Evaluation Rate Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Results Evaluated</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $resultEvaluationRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: rgba(180, 122, 66, 0.1); color: #B47A42;">
                            {{ $resultCount }} Marksheets
                        </span>
                        <span class="text-stone-400 font-medium">Ready</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="transition-all duration-1000 ease-out" style="color: #B47A42;" stroke-dasharray="{{ $resultEvaluationRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $resultEvaluationRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Scale of Papers & Groups Ring --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Curriculum Papers</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $paperCount }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $examGroupCount }} Groups
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
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $paperCount }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY EXAMINATION ANALYSIS CIRCLES (Class-wise Distribution + Lifecycle Gauge) ── --}}
    <div class="dashboard-grid-2">

        {{-- Circle 1: Class-Wise Exam Distribution (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            Classroom Distribution
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Class-Wise Exam Allocation</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $examCount }} Total Exams
                    </span>
                </div>

                {{-- Interactive Donut Chart Container --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="classExamsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-2">
                    @forelse($classExamsData as $cItem)
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
                    <div class="col-span-3 text-xs text-stone-400 py-3 text-center">No exams scheduled for this session</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Exams published across all academic grades</span>
                <a href="{{ route('examination.exams.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View all exams</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        {{-- Circle 2: Examination Lifecycle & Evaluation Health (Radial Gauge) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            Operations Health
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Lifecycle & Evaluation Pipeline</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $activeExamsCount }} Active
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[220px]">
                    <div id="examHealthRadialChart" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Health Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Active</span>
                        <span class="text-base font-black text-emerald-600 block mt-0.5">{{ $activeExamsRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $activeExamsCount }} of {{ $examCount }}</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Admit Cards</span>
                        <span class="text-base font-black text-blue-600 block mt-0.5">{{ $admitCardCoverageRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $admitCardCount }} Issued</span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border" style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-wider block">Evaluated</span>
                        <span class="text-base font-black block mt-0.5" style="color: #B47A42;">{{ $resultEvaluationRate }}%</span>
                        <span class="text-[10px] text-stone-400 mt-0.5 block">{{ $resultCount }} Marksheets</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-500 font-medium">Evaluation pipeline and operational readiness</span>
                <a href="{{ route('examination.results.index') }}" class="font-bold inline-flex items-center gap-1 hover:underline" style="color: #B47A42;">
                    <span>View marksheets</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SECONDARY EXAMINATION ANALYSIS CIRCLES (3-COLUMN GRID) ── --}}
    <div class="dashboard-grid-3">

        {{-- Circle 3A: Exams by Exam Group (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                            Assessment Groups
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Exam Groups Allocation</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #F3E8FF; color: #7E22CE;">
                        {{ $examGroupCount }} Groups
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="examGroupsDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                    @forelse($examGroupsData as $gItem)
                    <div class="flex items-center justify-between text-xs py-1.5 px-2 rounded-lg border border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $gItem['color'] }};"></span>
                            <span class="font-bold text-stone-800 truncate max-w-[120px]">{{ $gItem['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-stone-900">{{ $gItem['count'] }}</span>
                            <span class="text-[10px] text-stone-400">({{ $gItem['percentage'] }}%)</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 text-center py-4">No exam groups configured</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Categorized by term/unit</span>
                <a href="{{ route('examination.groups.index') }}" class="font-bold underline" style="color: #B47A42;">Manage Groups</a>
            </div>
        </div>

        {{-- Circle 3B: Student Performance & Pass Rate Health (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            Student Health
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Performance & Pass Rate</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $passRate }}% Pass
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="examPassRateDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Passed vs Failed Stats --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-emerald-50/70 border border-emerald-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-emerald-900">Passed Subjects</span>
                        </div>
                        <span class="font-black text-emerald-900">{{ $passedItems }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-rose-50/70 border border-rose-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <span class="font-bold text-rose-900">Failed / Below Threshold</span>
                        </div>
                        <span class="font-black text-rose-900">{{ $failedItems }}</span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] px-2 text-stone-400">
                        <span>Total Evaluated Papers:</span>
                        <span class="font-bold text-stone-700">{{ $totalResultItems }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Grading performance scale</span>
                <a href="{{ route('examination.results.index') }}" class="font-bold underline" style="color: #B47A42;">Enter Results</a>
            </div>
        </div>

        {{-- Circle 3C: Admit Cards & Hall Ticket Issuance Share (Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3.5 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="contact-round" class="w-4 h-4"></i>
                            Hall Tickets
                        </div>
                        <h2 class="text-base font-bold mt-0.5" style="color: #211B17;">Admit Cards Issuance</h2>
                    </div>
                    <span class="text-[11px] px-2 py-0.5 rounded font-extrabold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $admitCardCount }} Cards
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="my-4 flex justify-center items-center">
                    <div id="admitCardShareDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Stats breakdown --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-blue-50/70 border border-blue-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="font-bold text-blue-900">Exams with Cards</span>
                        </div>
                        <span class="font-black text-blue-900">{{ $examsWithAdmitCardsCount }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-stone-50 border border-stone-200">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-stone-400"></span>
                            <span class="font-bold text-stone-700">Cards Pending</span>
                        </div>
                        <span class="font-black text-stone-700">{{ max(0, $examCount - $examsWithAdmitCardsCount) }}</span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] px-2 text-stone-400">
                        <span>Enrolled Students in Session:</span>
                        <span class="font-bold text-stone-700">{{ $totalStudents }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <span class="text-stone-400">Hall ticket generation</span>
                <a href="{{ route('examination.admit-cards.index') }}" class="font-bold underline" style="color: #B47A42;">Generate Cards</a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: OPERATIONS & MANAGEMENT HUB (4 Quick Action Cards) ── --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-stone-900">Examination Operations Hub</h2>
                <p class="text-xs text-stone-500">Direct shortcuts to examination management, schedules, and reports</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- 1. Exams Management --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #EFF6FF; color: #1D4ED8;">
                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $examCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Manage Exams</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Published exam timetables, papers, and pass criteria</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('examination.exams.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View Exams</a>
                    <a href="{{ route('examination.exams.create') }}" class="text-xs font-bold px-2 py-1 rounded-md text-white" style="background: #B47A42;">+ Add</a>
                </div>
            </div>

            {{-- 2. Admit Cards --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #ECFDF5; color: #047857;">
                        <i data-lucide="contact-round" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $admitCardCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Admit Cards</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Generate and print roll numbers & student hall tickets</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('examination.admit-cards.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">View Admit Cards</a>
                    <span class="text-[11px] font-semibold text-stone-400">{{ $examsWithAdmitCardsCount }} Done</span>
                </div>
            </div>

            {{-- 3. Exam Results --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #FFFBEB; color: #B45309;">
                        <i data-lucide="table-properties" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $resultCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Exam Results</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Input subject marks, calculate ranks, and export marksheets</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('examination.results.index') }}" class="text-xs font-bold text-amber-700 hover:underline">View Results</a>
                    <span class="text-[11px] font-semibold text-stone-400">{{ $passRate }}% Pass</span>
                </div>
            </div>

            {{-- 4. Exam Groups --}}
            <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                 style="border-color: #E7E0D8;">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                         style="background: #F3E8FF; color: #7E22CE;">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <span class="text-xl font-black text-stone-900">{{ $examGroupCount }}</span>
                </div>
                <div class="mt-3">
                    <h3 class="text-sm font-bold text-stone-900">Exam Groups</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Configure grading scales, weightage, and grade criteria</p>
                </div>
                <div class="mt-4 pt-3 border-t flex items-center justify-between" style="border-color: #F0EAE1;">
                    <a href="{{ route('examination.groups.index') }}" class="text-xs font-bold text-purple-700 hover:underline">Configure Groups</a>
                    <span class="text-[11px] font-semibold text-stone-400">{{ $paperCount }} Papers</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: RECENT EXAMINATIONS DIRECTORY ── --}}
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden" style="border-color: #E7E0D8;">
        <div class="p-5 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold text-stone-900">Active & Scheduled Examinations</h3>
                <p class="text-xs text-stone-500">Overview of examinations scheduled for {{ $session->name }}</p>
            </div>
            <a href="{{ route('examination.exams.index') }}"
               class="text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border hover:bg-stone-50 transition"
               style="border-color: #DED5CB; color: #211B17;">
                <span>View Full List ({{ $examCount }})</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-stone-50/70 text-stone-600 font-bold uppercase tracking-wider text-[11px]" style="border-color: #F0EAE1;">
                        <th class="py-3 px-4">Exam Name</th>
                        <th class="py-3 px-4">Class & Sections</th>
                        <th class="py-3 px-4">Exam Group</th>
                        <th class="py-3 px-4 text-center">Papers</th>
                        <th class="py-3 px-4 text-center">Admit Cards</th>
                        <th class="py-3 px-4 text-center">Results</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 font-medium text-stone-800">
                    @forelse($exams->take(8) as $examItem)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="py-3.5 px-4 font-bold text-stone-900">
                            {{ $examItem->title }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-semibold text-[11px]"
                                  style="background: #EFF6FF; color: #1D4ED8;">
                                {{ $examItem->schoolClass?->name ?? 'All Classes' }}
                            </span>
                            @if($examItem->sections && $examItem->sections->count() > 0)
                                <span class="text-stone-400 text-[11px]">({{ $examItem->sections->pluck('name')->join(', ') }})</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($examItem->examGroup)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded font-medium text-[11px]"
                                      style="background: #F3E8FF; color: #7E22CE;">
                                    {{ $examItem->examGroup->name }}
                                </span>
                            @else
                                <span class="text-stone-400 text-[11px]">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-stone-800">{{ $examItem->papers_count }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($examItem->admit_cards_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                    {{ $examItem->admit_cards_count }} Issued
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-medium text-[11px] bg-stone-100 text-stone-500">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($examItem->results_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-amber-100 text-amber-800">
                                    {{ $examItem->results_count }} Saved
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-medium text-[11px] bg-stone-100 text-stone-500">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(strtolower((string) $examItem->status) === 'active' || $examItem->status == '1')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[11px] bg-stone-100 text-stone-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('examination.exams.edit', $examItem) }}"
                                   class="px-2 py-1 rounded text-[11px] font-bold border hover:bg-stone-100 text-stone-700"
                                   style="border-color: #DED5CB;">
                                    Edit
                                </a>
                                <a href="{{ route('examination.results.generate-form', $examItem) }}"
                                   class="px-2 py-1 rounded text-[11px] font-bold text-white hover:brightness-105"
                                   style="background: #B47A42;">
                                    Results
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-stone-400">
                            No examinations created for this academic session yet.
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

    // ── 1. Class-Wise Exam Distribution Donut Chart ──
    const classChartEl = document.querySelector("#classExamsDonutChart");
    if (classChartEl) {
        const classData = @json($classExamsData);
        const series = classData.map(item => item.count);
        const labels = classData.map(item => item.name);
        const colors = classData.map(item => item.color);
        const totalExams = {{ (int) $examCount }};

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
                                    label: 'Exams',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalExams;
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
                            return val + ' Exams';
                        }
                    }
                }
            };
            const chart = new ApexCharts(classChartEl, options);
            chart.render();
        } else {
            classChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No exams scheduled</div>';
        }
    }

    // ── 2. Examination Lifecycle & Health Multi-Radial Bar ──
    const healthChartEl = document.querySelector("#examHealthRadialChart");
    if (healthChartEl) {
        const activeRate = {{ (float) $activeExamsRate }};
        const admitRate = {{ (float) $admitCardCoverageRate }};
        const evalRate = {{ (float) $resultEvaluationRate }};

        const options = {
            series: [activeRate, admitRate, evalRate],
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
                            label: 'Overall',
                            color: '#766C63',
                            fontSize: '11px',
                            fontWeight: 700,
                            formatter: function (w) {
                                const avg = Math.round((activeRate + admitRate + evalRate) / 3);
                                return avg + '%';
                            }
                        }
                    }
                }
            },
            colors: ['#10B981', '#3B82F6', '#B47A42'],
            labels: ['Active Exams', 'Admit Cards', 'Evaluated'],
            stroke: { lineCap: 'round' }
        };
        const chart = new ApexCharts(healthChartEl, options);
        chart.render();
    }

    // ── 3. Exam Groups Distribution Donut Chart ──
    const groupChartEl = document.querySelector("#examGroupsDonutChart");
    if (groupChartEl) {
        const groupsData = @json($examGroupsData);
        const series = groupsData.map(item => item.count);
        const labels = groupsData.map(item => item.name);
        const colors = groupsData.map(item => item.color);
        const totalGroups = {{ (int) $examGroupCount }};

        if (series.length > 0 && series.reduce((a, b) => a + b, 0) > 0) {
            const options = {
                series: series,
                labels: labels,
                colors: colors.length > 0 ? colors : ['#8B5CF6', '#B47A42', '#10B981', '#3B82F6'],
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
                                    label: 'Groups',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalGroups;
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
                            return val + ' Exams';
                        }
                    }
                }
            };
            const chart = new ApexCharts(groupChartEl, options);
            chart.render();
        } else {
            groupChartEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No exam groups</div>';
        }
    }

    // ── 4. Exam Results Pass Rate Donut Chart ──
    const passRateEl = document.querySelector("#examPassRateDonutChart");
    if (passRateEl) {
        const passed = {{ (int) $passedItems }};
        const failed = {{ (int) $failedItems }};
        const totalItems = {{ (int) $totalResultItems }};
        const passRate = {{ (float) $passRate }};

        const hasResults = totalItems > 0;
        const series = hasResults ? [passed, failed] : [1];
        const labels = hasResults ? ['Passed', 'Failed'] : ['No Results'];
        const colors = hasResults ? ['#10B981', '#EF4444'] : ['#E5E7EB'];

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
                                label: hasResults ? 'Pass Rate' : 'Status',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
                                formatter: function () {
                                    return hasResults ? passRate + '%' : '0';
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
                        return hasResults ? val + ' Students' : 'No data';
                    }
                }
            }
        };
        const chart = new ApexCharts(passRateEl, options);
        chart.render();
    }

    // ── 5. Admit Cards Share Donut Chart ──
    const admitShareEl = document.querySelector("#admitCardShareDonutChart");
    if (admitShareEl) {
        const issued = {{ (int) $examsWithAdmitCardsCount }};
        const pending = {{ max(0, $examCount - $examsWithAdmitCardsCount) }};
        const total = {{ (int) $examCount }};
        const coverageRate = {{ (float) $admitCardCoverageRate }};

        const hasExams = total > 0;
        const series = hasExams ? [issued, pending] : [1];
        const labels = hasExams ? ['Issued', 'Pending'] : ['No Exams'];
        const colors = hasExams ? ['#3B82F6', '#D1D5DB'] : ['#E5E7EB'];

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
                                label: 'Coverage',
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
                        return hasExams ? val + ' Exams' : '0';
                    }
                }
            }
        };
        const chart = new ApexCharts(admitShareEl, options);
        chart.render();
    }

});
</script>
@endsection