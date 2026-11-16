@extends('layouts.app')

@section('header', 'Academic Dashboard')

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
                    <span>Curriculum, Classrooms & Instruction</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: #FAF6F0; border: 1px solid #E7E0D8;">
                        <i data-lucide="graduation-cap" class="w-5 h-5" style="color: #B47A42;"></i>
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black tracking-tight" style="color: #211B17;">
                            Academic Overview — {{ $school->name }}
                        </h1>
                        <p class="text-xs font-medium" style="color: #766C63;">
                            Managing curriculum, teacher allotments, syllabus materials, and classrooms.
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
                    <form method="GET" action="{{ route('academic.dashboard') }}" class="inline-block m-0">
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
                <a href="{{ route('academic.subjects.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #171411; color: #ffffff;">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Manage Subjects</span>
                </a>

                <a href="{{ route('academic.class-teachers.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl shadow-sm transition-all hover:brightness-105"
                   style="background: #B47A42; color: #ffffff;">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Assign Teachers</span>
                </a>
            </div>

        </div>

        @if($globalCurrentSession && $session && $globalCurrentSession->id !== $session->id)
        <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #E7E0D8;">
            <span class="text-amber-700 font-medium">
                Viewing archived academic data for <strong>{{ $session->name }}</strong>
            </span>
            <a href="{{ route('academic.dashboard', ['session_id' => $globalCurrentSession->id]) }}"
               class="text-xs font-bold underline" style="color: #B47A42;">
                Back to Current ({{ $globalCurrentSession->name }})
            </a>
        </div>
        @endif
    </div>

    {{-- ── 4 KEY ACADEMIC KPI CIRCULAR METRIC CARDS (Animated SVG Rings) ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Class Teacher Allocation Coverage Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Teacher Coverage</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $teacherCoverageRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $assignedClassesCount }} of {{ $totalClasses }}
                        </span>
                        <span class="text-stone-400 font-medium">Classes</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="{{ $teacherCoverageRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $teacherCoverageRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Curriculum Breadth (Subjects Count) Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Curriculum Breadth</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $totalSubjects }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #EFF6FF; color: #1D4ED8;">
                            {{ count($subjectTypesData) }} Types
                        </span>
                        <span class="text-stone-400 font-medium">Offered</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-blue-500 transition-all duration-1000 ease-out" stroke-dasharray="90, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $totalSubjects }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Study Materials Coverage Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Materials Coverage</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $materialsCoverageRate }}%</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: rgba(180, 122, 66, 0.1); color: #B47A42;">
                            {{ $totalMaterials }} Files
                        </span>
                        <span class="text-stone-400 font-medium">Uploaded</span>
                    </div>
                </div>

                {{-- Circular Progress Ring --}}
                <div class="relative w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="transition-all duration-1000 ease-out" style="color: #B47A42;" stroke-dasharray="{{ $materialsCoverageRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $materialsCoverageRate }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Classroom Sections Scale Circle --}}
        <div class="bg-white rounded-2xl p-5 border shadow-sm hover:shadow-md transition-all duration-200"
             style="border-color: #E7E0D8;">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Active Sections</span>
                    <h3 class="text-2xl font-black mt-1" style="color: #211B17;">{{ $totalSections }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full"
                              style="background: #F3E8FF; color: #7E22CE;">
                            {{ $totalClasses }} Grades
                        </span>
                        <span class="text-stone-400 font-medium">Enrolled</span>
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
                        <span class="text-[11px] font-black" style="color: #211B17;">{{ $totalSections }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── SECTION 1: PRIMARY ACADEMIC ANALYSIS CIRCLES (Subject Types + Teacher Coverage) ── --}}
    <div class="dashboard-grid-2">

        {{-- Circle 1: Subject Types & Curriculum Share (Interactive Donut Chart) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            Curriculum Structure
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Subject Types & Distribution</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $totalSubjects }} Total Subjects
                    </span>
                </div>

                {{-- Interactive Donut Chart --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[240px]">
                    <div id="subjectTypesDonutChart" class="w-full flex justify-center"></div>
                </div>

                {{-- Breakdown Pills --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-2">
                    @forelse($subjectTypesData as $st)
                    <div class="p-2.5 rounded-xl border flex items-center justify-between"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $st['color'] }};"></span>
                            <div class="truncate">
                                <span class="text-xs font-bold text-stone-800 block truncate">{{ $st['name'] }}</span>
                                <span class="text-[10px] text-stone-500 font-medium">{{ $st['percentage'] }}%</span>
                            </div>
                        </div>
                        <span class="text-sm font-black text-stone-900 ml-1">{{ $st['count'] }}</span>
                    </div>
                    @empty
                    <div class="col-span-3 text-xs text-stone-400 py-3 text-center">No subject types registered</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1; color: #766C63;">
                <span>Total Subjects: <strong style="color: #211B17;">{{ $totalSubjects }} Subjects</strong></span>
                <span>Across <strong style="color: #211B17;">{{ $totalClasses }} Academic Classes</strong></span>
            </div>
        </div>

        {{-- Circle 2: Class Teachers Allocation & Staff Coverage (Radial / Gauge Meter) --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between w-full"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-4 border-b" style="border-color: #F0EAE1;">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider" style="color: #B47A42;">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            Instructional Leadership
                        </div>
                        <h2 class="text-lg font-bold mt-0.5" style="color: #211B17;">Class Teacher Allocation Health</h2>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                          style="background: #ECFDF5; color: #047857;">
                        {{ $teacherCoverageRate }}% Covered
                    </span>
                </div>

                {{-- Interactive Radial Meter --}}
                <div class="flex flex-col items-center justify-center my-4 min-h-[240px]">
                    <div id="teacherCoverageRadialChart" class="w-full flex justify-center"></div>
                </div>

                {{-- 3 Key Allocation Tiles --}}
                <div class="grid grid-cols-3 gap-2.5 mt-2">
                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: #FAF6F0; border-color: #E7E0D8;">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500 block">Total Classes</span>
                        <span class="text-xs font-black block mt-0.5" style="color: #211B17;">
                            {{ $totalClasses }}
                        </span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: #ECFDF5; border-color: #A7F3D0;">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Assigned</span>
                        <span class="text-xs font-black text-emerald-800 block mt-0.5">
                            {{ $assignedClassesCount }}
                        </span>
                    </div>

                    <div class="rounded-xl p-2.5 text-center border"
                         style="background: {{ $unassignedClassesCount > 0 ? '#FFF1F2' : '#F9FAFB' }}; border-color: {{ $unassignedClassesCount > 0 ? '#FECDD3' : '#E5E7EB' }};">
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $unassignedClassesCount > 0 ? 'text-rose-700' : 'text-stone-500' }} block">Unassigned</span>
                        <span class="text-xs font-black {{ $unassignedClassesCount > 0 ? 'text-rose-800' : 'text-stone-700' }} block mt-0.5">
                            {{ $unassignedClassesCount }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: #F0EAE1;">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px]"
                          style="background: #EFF6FF; color: #1D4ED8;">
                        {{ $totalClassTeachers }} Faculty Teachers
                    </span>
                    <span class="text-stone-400 text-[11px]">in active charge</span>
                </div>
                <a href="{{ route('academic.class-teachers.index') }}"
                   class="text-xs font-bold underline hover:opacity-80" style="color: #B47A42;">
                    Manage Allocations &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 2: SECONDARY ACADEMIC ANALYSIS CIRCLES (Materials + Class Subject Load + Leaves) ── --}}
    <div class="dashboard-grid-3">

        {{-- Circle 3A: Study Materials by Format Circle --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3 border-b" style="border-color: #F0EAE1;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-amber-600"
                             style="background: #FFFBEB;">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #211B17;">Study Materials</h3>
                            <span class="text-[11px] text-stone-400">Content Format Share</span>
                        </div>
                    </div>
                    <span class="text-xs font-black px-2 py-0.5 rounded-full"
                          style="background: #FFFBEB; color: #B45309;">
                        {{ $totalMaterials }} Files
                    </span>
                </div>

                {{-- Chart --}}
                <div id="materialFormatsChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Formats list --}}
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    @forelse($materialTypesData as $fmt)
                    <div class="p-2 rounded-lg border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $fmt['color'] }};"></span>
                            <span class="font-bold text-stone-800">{{ $fmt['name'] }}</span>
                        </div>
                        <span class="font-black" style="color: #B47A42;">{{ $fmt['count'] }} ({{ $fmt['percentage'] }}%)</span>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 py-4 text-center">No study materials uploaded</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex justify-between" style="border-color: #F0EAE1;">
                <span>Grades Covered: <strong class="text-amber-600">{{ $classesWithMaterialsCount }} of {{ $totalClasses }}</strong></span>
                <span>Coverage: <strong class="text-blue-600">{{ $materialsCoverageRate }}%</strong></span>
            </div>
        </div>

        {{-- Circle 3B: Class-wise Subject Load Distribution Circle --}}
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
                            <h3 class="text-sm font-bold" style="color: #211B17;">Class Subject Load</h3>
                            <span class="text-[11px] text-stone-400">Subjects per Grade</span>
                        </div>
                    </div>
                    <span class="text-xs font-black px-2 py-0.5 rounded-full"
                          style="background: #FAF5FF; color: #7E22CE;">
                        Top {{ $classesWithSubjects->count() }} Classes
                    </span>
                </div>

                {{-- Chart --}}
                <div id="classSubjectsLoadChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Classes List --}}
                <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    @forelse($classesWithSubjects as $cls)
                    <div class="p-2 rounded-lg border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">{{ $cls->name }}</span>
                        <span class="font-black text-purple-700">{{ $cls->subjects_count }} Subjects</span>
                    </div>
                    @empty
                    <div class="text-xs text-stone-400 py-4 text-center">No subjects mapped to classes</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex justify-between" style="border-color: #F0EAE1;">
                <span>Total Classes: <strong class="text-purple-600">{{ $totalClasses }}</strong></span>
                <span>Total Subjects: <strong style="color: #211B17;">{{ $totalSubjects }}</strong></span>
            </div>
        </div>

        {{-- Circle 3C: Student Leaves Approval & Attendance Health Circle --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm flex flex-col justify-between"
             style="border-color: #E7E0D8;">
            <div>
                <div class="flex items-center justify-between pb-3 border-b" style="border-color: #F0EAE1;">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-emerald-600"
                             style="background: #ECFDF5;">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold" style="color: #211B17;">Student Leaves</h3>
                            <span class="text-[11px] text-stone-400">Approval Processing</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #ECFDF5; color: #047857;">
                            {{ $approvedStudentLeaves }} Appr
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #FFFBEB; color: #B45309;">
                            {{ $pendingStudentLeaves }} Pend
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              style="background: #FFF1F2; color: #BE123C;">
                            {{ $rejectedStudentLeaves }} Rej
                        </span>
                    </div>
                </div>

                {{-- Chart --}}
                <div id="academicLeavesChart" class="min-h-[190px] flex items-center justify-center my-2"></div>

                {{-- Highlights --}}
                <div class="space-y-2 pt-2">
                    <div class="p-2 rounded-xl border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">Approved Leaves</span>
                        <span class="font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">{{ $approvedStudentLeaves }} ({{ $leaveApprovalRate }}%)</span>
                    </div>

                    <div class="p-2 rounded-xl border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">Pending Requests</span>
                        <span class="font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded">{{ $pendingStudentLeaves }} Requests</span>
                    </div>

                    <div class="p-2 rounded-xl border flex items-center justify-between text-xs"
                         style="background: #FAF8F5; border-color: #F0EAE1;">
                        <span class="font-bold text-stone-800">Rejected Requests</span>
                        <span class="font-black text-rose-700 bg-rose-50 px-2 py-0.5 rounded">{{ $rejectedStudentLeaves }} Requests</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t text-[11px] text-stone-500 flex justify-between" style="border-color: #F0EAE1;">
                <span>Total Applications: <strong style="color: #211B17;">{{ $totalStudentLeaves }}</strong></span>
                <a href="{{ route('academic.student-leaves.index') }}" class="text-emerald-700 font-bold underline">
                    View Leaves &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- ── SECTION 3: ACADEMIC OPERATIONS & QUICK ACTIONS HUB ── --}}
    <div class="rounded-2xl p-6 border shadow-sm"
         style="background: #ffffff; border-color: #E7E0D8;">
        
        <div class="flex items-center justify-between pb-4 border-b mb-6" style="border-color: #F0EAE1;">
            <div>
                <h3 class="text-base font-bold flex items-center gap-2" style="color: #211B17;">
                    <i data-lucide="layout-grid" class="w-4 h-4" style="color: #B47A42;"></i>
                    Academic Operations & Course Management
                </h3>
                <p class="text-xs text-stone-500 mt-0.5">
                    Direct access to class sections, subject curriculums, syllabi, and communication boards.
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full"
                  style="background: rgba(180, 122, 66, 0.1); color: #B47A42;">
                Active Academic Session
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- 1. Class Sections --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-blue-600 bg-blue-50">
                            <i data-lucide="columns-3" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Class Sections</h4>
                            <span class="text-[11px] text-stone-400">{{ $totalSections }} Sections in {{ $totalClasses }} Classes</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.class-sections.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg text-white transition-all hover:brightness-105"
                       style="background: #B47A42;">
                        <i data-lucide="settings-2" class="w-3 h-3"></i>
                        Manage Sections
                    </a>
                </div>
            </div>

            {{-- 2. Subjects --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-purple-600 bg-purple-50">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Subjects & Curriculum</h4>
                            <span class="text-[11px] text-stone-400">{{ $totalSubjects }} Subjects Registered</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.subjects.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg text-white"
                       style="background: #171411;">
                        View All
                    </a>
                    <a href="{{ route('academic.subject-types.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg border bg-white text-stone-700 hover:bg-stone-50">
                        Subject Types
                    </a>
                </div>
            </div>

            {{-- 3. Study Materials --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-amber-600 bg-amber-50">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Study Materials</h4>
                            <span class="text-[11px] text-stone-400">{{ $totalMaterials }} Files Uploaded</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.study-materials.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg text-white"
                       style="background: #B47A42;">
                        Browse Library
                    </a>
                    <a href="{{ route('academic.study-materials.create') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg border bg-white text-stone-700 hover:bg-stone-50">
                        Upload New
                    </a>
                </div>
            </div>

            {{-- 4. Manage Mediums --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-teal-600 bg-teal-50">
                            <i data-lucide="languages" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Instruction Mediums</h4>
                            <span class="text-[11px] text-stone-400">{{ $mediumsCount }} Mediums Configured</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.mediums.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg border bg-white text-stone-700 hover:bg-stone-50">
                        Manage Mediums
                    </a>
                </div>
            </div>

            {{-- 5. Notice Board --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-rose-600 bg-rose-50">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Notice Board</h4>
                            <span class="text-[11px] text-stone-400">{{ $totalNotices }} Total ({{ $activeNotices }} Active)</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.noticeboards.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg text-white"
                       style="background: #171411;">
                        View Notices
                    </a>
                    <a href="{{ route('academic.noticeboards.index') }}?open_add=1"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg border bg-white text-stone-700 hover:bg-stone-50">
                        Publish Notice
                    </a>
                </div>
            </div>

            {{-- 6. Teacher Allocations --}}
            <div class="rounded-xl border p-5 transition-all hover:shadow-md"
                 style="background: #FAF8F5; border-color: #E7E0D8;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-indigo-600 bg-indigo-50">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-stone-900">Class Teachers Roster</h4>
                            <span class="text-[11px] text-stone-400">{{ $assignedClassesCount }} of {{ $totalClasses }} Classes Assigned</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-stone-200">
                    <a href="{{ route('academic.class-teachers.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg text-white"
                       style="background: #B47A42;">
                        Assign Teachers
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ── ApexCharts Script & Interactive Charts Initialization ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Subject Types Donut Chart ──
    const subjectTypesEl = document.querySelector("#subjectTypesDonutChart");
    if (subjectTypesEl) {
        const typesData = @json($subjectTypesData);
        const totalSubs = {{ (int) $totalSubjects }};

        if (typesData.length > 0 && totalSubs > 0) {
            const series = typesData.map(item => item.count);
            const labels = typesData.map(item => item.name);
            const colors = typesData.map(item => item.color);

            const options = {
                series: series,
                labels: labels,
                colors: colors,
                chart: {
                    type: 'donut',
                    height: 240,
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
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
                                    fontSize: '24px',
                                    fontWeight: 900,
                                    color: '#211B17',
                                    offsetY: 8
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Subjects',
                                    fontSize: '12px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalSubs;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { width: 3, colors: ['#ffffff'] },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) {
                            return val + ' Subjects';
                        }
                    }
                }
            };
            const chart = new ApexCharts(subjectTypesEl, options);
            chart.render();
        } else {
            subjectTypesEl.innerHTML = '<div class="text-xs text-stone-400 py-8 text-center">No subjects found in this session</div>';
        }
    }

    // ── 2. Class Teacher Allocation Radial Gauge ──
    const teacherRadialEl = document.querySelector("#teacherCoverageRadialChart");
    if (teacherRadialEl) {
        const coverageRate = {{ (float) $teacherCoverageRate }};
        const assignedClasses = {{ (int) $assignedClassesCount }};
        const totalClasses = {{ (int) $totalClasses }};

        const radialOptions = {
            series: [coverageRate],
            chart: {
                type: 'radialBar',
                height: 240,
                sparkline: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -125,
                    endAngle: 125,
                    hollow: {
                        margin: 0,
                        size: '72%',
                        background: '#ffffff'
                    },
                    track: {
                        background: '#FAF6F0',
                        strokeWidth: '97%',
                        margin: 5
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            label: 'Coverage',
                            fontSize: '12px',
                            fontWeight: 700,
                            color: '#766C63',
                            offsetY: -8
                        },
                        value: {
                            show: true,
                            offsetY: 6,
                            fontSize: '28px',
                            fontWeight: 900,
                            color: '#211B17',
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            fill: {
                colors: [coverageRate >= 80 ? '#10B981' : (coverageRate >= 50 ? '#B47A42' : '#EF4444')]
            },
            stroke: { lineCap: 'round' },
            labels: ['Coverage Rate']
        };
        const radialChart = new ApexCharts(teacherRadialEl, radialOptions);
        radialChart.render();
    }

    // ── 3. Study Material Formats Donut Chart ──
    const materialFmtEl = document.querySelector("#materialFormatsChart");
    if (materialFmtEl) {
        const matData = @json($materialTypesData);
        const totalMats = {{ (int) $totalMaterials }};

        if (matData.length > 0 && totalMats > 0) {
            const series = matData.map(m => m.count);
            const labels = matData.map(m => m.name);
            const colors = matData.map(m => m.color);

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
                                    label: 'Files',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalMats;
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
                            return val + ' Files';
                        }
                    }
                }
            };
            const chart = new ApexCharts(materialFmtEl, options);
            chart.render();
        } else {
            materialFmtEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No study materials uploaded</div>';
        }
    }

    // ── 4. Class-wise Subject Load Donut Chart ──
    const classSubsEl = document.querySelector("#classSubjectsLoadChart");
    if (classSubsEl) {
        const classLabels = @json($classesWithSubjects->pluck('name')->values());
        const classSeries = @json($classesWithSubjects->pluck('subjects_count')->values());
        const totalSubs = {{ (int) $totalSubjects }};

        if (classSeries.length > 0 && classSeries.reduce((a, b) => a + b, 0) > 0) {
            const options = {
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
                                value: { show: true, fontSize: '18px', fontWeight: 800, color: '#211B17', offsetY: 6 },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Subjects',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    color: '#766C63',
                                    formatter: function () {
                                        return totalSubs;
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
                            return val + ' Subjects';
                        }
                    }
                }
            };
            const chart = new ApexCharts(classSubsEl, options);
            chart.render();
        } else {
            classSubsEl.innerHTML = '<div class="text-xs text-stone-400 py-6 text-center">No subject mappings</div>';
        }
    }

    // ── 5. Student Leaves Approval Health Donut Chart ──
    const academicLeavesEl = document.querySelector("#academicLeavesChart");
    if (academicLeavesEl) {
        const approvedLeaves = {{ (int) $approvedStudentLeaves }};
        const pendingLeaves = {{ (int) $pendingStudentLeaves }};
        const rejectedLeaves = {{ (int) $rejectedStudentLeaves }};
        const totalLeaves = {{ (int) $totalStudentLeaves }};

        const hasLeaves = totalLeaves > 0;
        const series = hasLeaves ? [approvedLeaves, pendingLeaves, rejectedLeaves] : [1];
        const labels = hasLeaves ? ['Approved', 'Pending', 'Rejected'] : ['No Leaves'];
        const colors = hasLeaves ? ['#10B981', '#F59E0B', '#EF4444'] : ['#E5E7EB'];

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
                                label: 'Leaves',
                                fontSize: '11px',
                                fontWeight: 700,
                                color: '#766C63',
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
                        return hasLeaves ? val + ' Requests' : '0';
                    }
                }
            }
        };
        const chart = new ApexCharts(academicLeavesEl, options);
        chart.render();
    }

});
</script>
@endsection
