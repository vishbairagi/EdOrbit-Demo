@extends('layouts.app')

@section('header', 'Exam Results')

@section('content')

@php
    $dateFormat = \App\Models\SystemSetting::getSetting('date_format', 'Y-m-d');
@endphp

{{-- ══ Header Breadcrumb & Top Actions ══ --}}
<div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center shadow-sm text-white">
            <i data-lucide="award" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">{{ $school->name }}</h2>
            <p class="text-xs text-body">
                Academic Session: <span class="font-semibold text-navy">{{ $session->name }}</span> &bull; Examination Results & Evaluation
            </p>
        </div>
    </div>

    {{-- Top Action Buttons & Session Switcher --}}
    <div class="flex items-center gap-2 self-stretch sm:self-auto flex-wrap">
        {{-- Session Selector Dropdown --}}
        <form method="GET" action="{{ route('examination.results.index') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 shadow-sm">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-navy"></i>
            <label class="text-xs font-semibold text-body whitespace-nowrap">Session:</label>
            <select name="session_id" onchange="this.form.submit()" class="text-xs font-bold text-navy bg-transparent border-none focus:outline-none focus:ring-0 cursor-pointer">
                @foreach($sessions as $s)
                    <option value="{{ $s->id }}" {{ $session->id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }} {{ $s->id == ($globalCurrentSessionId ?? null) ? '★ (Current)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <button type="button" onclick="openResultAnalysisModal('exams')"
                class="inline-flex items-center justify-center px-3.5 py-2 bg-white border border-gray-200 text-heading text-xs font-semibold rounded-lg hover:bg-gray-50 shadow-sm transition-all"
                title="View deep analysis of results for this session">
            <i data-lucide="bar-chart-2" class="w-4 h-4 mr-1.5 text-navy"></i>
            Session Analytics
        </button>

        <a href="{{ route('examination.exams.index') }}"
           class="inline-flex items-center justify-center px-3.5 py-2 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark shadow-sm transition-all">
            <i data-lucide="list" class="w-4 h-4 mr-1.5"></i>
            View All Exams
        </a>
    </div>
</div>

{{-- ══ Alerts: Success / Error ══ --}}
@if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-5 rounded-r-xl shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2.5 text-emerald-800 text-sm font-medium">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2.5 text-red-800 text-sm font-medium">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

{{-- ══ 4 KEY SESSION ANALYSIS BOXES ══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Box 1: Total Results Logged --}}
    <div onclick="openResultAnalysisModal('exams')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-navy/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-navy">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Results Logged</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-navy transition-colors">
                    {{ number_format($analytics['totalResults']) }}
                    <span class="text-xs font-semibold text-stone-500">Results</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        {{ $analytics['totalStudents'] }} Students Enrolled
                    </span>
                </div>
            </div>

            {{-- Circular Metric Graphic --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="w-16 h-16 rounded-full bg-amber-50/80 border-2 border-amber-200/80 flex items-center justify-center text-navy shadow-inner">
                    <i data-lucide="file-check-2" class="w-7 h-7 text-navy"></i>
                </div>
                <span class="text-[10px] font-bold text-navy uppercase tracking-tight mt-1 text-center">Session</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Session: <strong>{{ $session->name }}</strong></span>
            <span class="text-navy group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Exams breakdown <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 2: Exams With Results (Evaluation Coverage) --}}
    <div onclick="openResultAnalysisModal('exams')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-700">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    <span>Exams With Results</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-emerald-700 transition-colors">
                    {{ $analytics['examsWithResults'] }} <span class="text-sm font-bold text-stone-400">/ {{ $analytics['totalExams'] }}</span>
                    <span class="text-xs font-semibold text-stone-500">Exams</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $analytics['evaluationRate'] }}% Evaluated
                    </span>
                    <span class="text-stone-400 text-[11px]">{{ $analytics['examsWithoutResults'] }} pending</span>
                </div>
            </div>

            {{-- Circular Progress Ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out"
                            stroke-dasharray="{{ $analytics['evaluationRate'] }}, 100" stroke-width="3.5"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-black text-emerald-700">{{ $analytics['evaluationRate'] }}%</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-tight mt-1 text-center">Ready</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Status distribution</span>
            <span class="text-emerald-600 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                View status <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 3: Exam of Months (Monthly Schedule Distribution) --}}
    <div onclick="openResultAnalysisModal('months')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-blue-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-700">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                    <span>Exams of Months</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-blue-700 transition-colors">
                    {{ $analytics['activeMonthsCount'] }}
                    <span class="text-xs font-semibold text-stone-500">Active Month{{ $analytics['activeMonthsCount'] == 1 ? '' : 's' }}</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    @if($analytics['examsThisMonth'] > 0)
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            {{ $analytics['examsThisMonth'] }} Exam{{ $analytics['examsThisMonth'] == 1 ? '' : 's' }} in {{ $analytics['currentMonthName'] }}
                        </span>
                    @elseif($analytics['peakMonth'])
                        <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            Peak: {{ $analytics['peakMonth']['label'] }} ({{ $analytics['peakMonth']['exam_count'] }})
                        </span>
                    @else
                        <span class="text-stone-400 text-[11px]">No exams scheduled</span>
                    @endif
                </div>
            </div>

            {{-- Circular Metric Graphic --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="w-16 h-16 rounded-full bg-blue-50 border-2 border-blue-200 flex items-center justify-center text-blue-700 shadow-inner">
                    <i data-lucide="calendar" class="w-7 h-7 text-blue-600"></i>
                </div>
                <span class="text-[10px] font-bold text-blue-700 uppercase tracking-tight mt-1 text-center">Months</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Monthly schedule timeline</span>
            <span class="text-blue-600 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Timeline breakdown <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 4: Class Reach & Logistics --}}
    <div onclick="openResultAnalysisModal('classes')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-purple-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-700">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Class Coverage</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-purple-700 transition-colors">
                    {{ $analytics['classesWithResultsCount'] }} <span class="text-sm font-bold text-stone-400">/ {{ $analytics['totalClassesCount'] }}</span>
                    <span class="text-xs font-semibold text-stone-500">Classes</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        {{ $analytics['classCoverageRate'] }}% Covered
                    </span>
                    <span class="text-stone-400 text-[11px]">Avg {{ $analytics['avgResultsPerExam'] }} / exam</span>
                </div>
            </div>

            {{-- Circular Progress Ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-purple-500 transition-all duration-1000 ease-out"
                            stroke-dasharray="{{ $analytics['classCoverageRate'] }}, 100" stroke-width="3.5"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-black text-purple-700">{{ $analytics['classCoverageRate'] }}%</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-purple-700 uppercase tracking-tight mt-1 text-center">Classes</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Class-wise distribution</span>
            <span class="text-purple-600 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Class details <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

</div>

{{-- ══ Filters and Search Bar ══ --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('examination.results.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 flex-1">
            {{-- Search Keyword --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-body mb-1">Search Exam:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Exam title, center, class, group..."
                           class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 pr-7 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('examination.results.index', array_filter(['session_id' => $session->id, 'class_id' => request('class_id'), 'month' => request('month'), 'result_status' => request('result_status'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Class Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Month Filter ("exam of months") --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Exam Month:</label>
                <select name="month" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Months</option>
                    @if(!empty($analytics['monthsBreakdown']))
                        @foreach($analytics['monthsBreakdown'] as $mb)
                            <option value="{{ $mb['key'] }}" {{ request('month') == $mb['key'] ? 'selected' : '' }}>
                                {{ $mb['label'] }} ({{ $mb['exam_count'] }})
                            </option>
                        @endforeach
                    @else
                        @foreach([1=>'January', 2=>'February', 3=>'March', 4=>'April', 5=>'May', 6=>'June', 7=>'July', 8=>'August', 9=>'September', 10=>'October', 11=>'November', 12=>'December'] as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ request('month') == $mNum ? 'selected' : '' }}>
                                {{ $mLabel }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Results Status Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Result Status:</label>
                <select name="result_status" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Statuses</option>
                    <option value="generated" {{ request('result_status') === 'generated' ? 'selected' : '' }}>Results Generated</option>
                    <option value="pending" {{ request('result_status') === 'pending' ? 'selected' : '' }}>Pending Generation</option>
                </select>
            </div>

            {{-- Per Page --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="10" {{ ($perPage ?? 15) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15 / page</option>
                    <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100 / page</option>
                </select>
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1 lg:pt-4">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'class_id', 'month', 'result_status', 'per_page']))
                <a href="{{ route('examination.results.index', ['session_id' => $session->id]) }}"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-100 text-heading text-xs font-medium hover:bg-gray-200 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ══ Bulk Delete Toolbar ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center justify-between bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <div class="flex items-center gap-3">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('examination.results.bulk-destroy-exams') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Generated Results
            </button>
        </form>
    </div>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear Selection</button>
</div>

{{-- ══ Exams Results Table ══ --}}
<section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-xs">
            <thead class="text-left text-white" style="background:var(--color-primary);">
                <tr>
                    <th class="p-3 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                               class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="p-3">Exam Title</th>
                    <th class="p-3">Class</th>
                    <th class="p-3">Exam Center</th>
                    <th class="p-3">Schedule & Month</th>
                    <th class="p-3 text-center">Generated Results</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($exams as $exam)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="p-3 text-center w-10">
                            <input type="checkbox" value="{{ $exam->id }}" onchange="updateBulkToolbar()"
                                   class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-heading text-sm">{{ $exam->title }}</div>
                            @if($exam->examGroup)
                                <span class="inline-flex items-center text-[10px] font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded mt-0.5">
                                    {{ $exam->examGroup->name }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 font-semibold text-heading px-2 py-1 rounded bg-stone-100">
                                <i data-lucide="book-open" class="w-3 h-3 text-navy"></i>
                                {{ $exam->schoolClass->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-3 font-medium text-body">
                            {{ $exam->exam_center ?: 'Main Campus' }}
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700">
                                    {{ $exam->start_date?->format('M Y') }}
                                </span>
                                <span class="text-body font-medium">
                                    {{ $exam->start_date?->format($dateFormat) }}
                                    @if($exam->end_date && $exam->end_date != $exam->start_date)
                                        &ndash; {{ $exam->end_date?->format($dateFormat) }}
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            @if($exam->results_count > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                                    {{ $exam->results_count }} Results
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i data-lucide="alert-circle" class="w-3 h-3 text-amber-500"></i>
                                    0 Pending
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5 flex-wrap">
                                <a href="{{ route('examination.results.generate-form', $exam) }}"
                                   class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 font-bold text-xs text-white shadow-sm hover:opacity-95 transition"
                                   style="background:var(--color-primary);"
                                   title="Generate or record exam results">
                                    <i data-lucide="square-pen" class="h-3.5 w-3.5"></i>
                                    <span>{{ $exam->results_count > 0 ? 'Enter More' : 'Generate Results' }}</span>
                                </a>

                                @if($exam->results_count > 0)
                                    <a href="{{ route('examination.results.view', $exam) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 font-bold text-xs text-heading hover:bg-gray-50 shadow-sm transition"
                                       title="View recorded exam results">
                                        <i data-lucide="eye" class="h-3.5 w-3.5 text-navy"></i>
                                        <span>View</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-stone-100 flex items-center justify-center text-stone-400 mb-3">
                                    <i data-lucide="search-x" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-sm font-bold text-heading">No exams found</h4>
                                <p class="text-xs text-body mt-1">
                                    No examinations matched your query for session <strong>{{ $session->name }}</strong>.
                                </p>
                                @if(request()->hasAny(['search', 'class_id', 'month', 'result_status']))
                                    <a href="{{ route('examination.results.index', ['session_id' => $session->id]) }}"
                                       class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        Clear All Filters
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ══ Pagination Footer Bar ══ --}}
    @if($exams->hasPages())
        <div class="p-4 bg-gray-50/70 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-body">
                Showing <strong>{{ $exams->firstItem() }}</strong> to <strong>{{ $exams->lastItem() }}</strong> of <strong>{{ $exams->total() }}</strong> exams
            </span>
            <div class="exam-pagination">
                {{ $exams->links() }}
            </div>
        </div>
    @elseif($exams->total() > 0)
        <div class="p-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs text-body">
            <span>Showing all <strong>{{ $exams->total() }}</strong> exam(s) for session <strong>{{ $session->name }}</strong></span>
            <span class="text-gray-400">EdOrbit Examination Management</span>
        </div>
    @endif
</section>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- INTERACTIVE SESSION ANALYSIS MODAL (resultAnalysisModal)          --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="resultAnalysisModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-scaleUp">
        
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Session Exam Results Analytics</h3>
                    <p class="text-xs text-white/70">Academic Session: {{ $session->name }} &bull; {{ $school->name }}</p>
                </div>
            </div>
            <button type="button" onclick="closeResultAnalysisModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Modal Navigation Tabs --}}
        <div class="flex border-b border-gray-200 bg-gray-50 px-6 pt-3 gap-3 overflow-x-auto">
            <button type="button" onclick="switchAnalysisTab('exams')" id="tab-exams-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-bold border-b-2 border-navy text-navy flex items-center gap-1.5 transition whitespace-nowrap">
                <i data-lucide="award" class="w-4 h-4"></i>
                Exam Results Analysis ({{ $analytics['totalExams'] }})
            </button>
            <button type="button" onclick="switchAnalysisTab('months')" id="tab-months-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-semibold text-gray-500 hover:text-navy border-b-2 border-transparent flex items-center gap-1.5 transition whitespace-nowrap">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                Exams of Months ({{ $analytics['activeMonthsCount'] }})
            </button>
            <button type="button" onclick="switchAnalysisTab('classes')" id="tab-classes-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-semibold text-gray-500 hover:text-navy border-b-2 border-transparent flex items-center gap-1.5 transition whitespace-nowrap">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                Class-Wise Breakdown ({{ count($analytics['classBreakdown']) }})
            </button>
        </div>

        {{-- Modal Content Area --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4">

            {{-- Tab 1: Exam-Wise Results Breakdown --}}
            <div id="tab-content-exams" class="space-y-4 animate-fadeIn">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <p class="text-xs text-body">
                        Detailed breakdown of results logged for each examination in <strong>{{ $session->name }}</strong>:
                    </p>
                    <input type="text" id="modalExamSearch" oninput="filterModalExams(this.value)" placeholder="Search exam or class..."
                           class="w-full sm:w-56 px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="modalExamGrid">
                    @forelse($analytics['allSessionExams'] as $ae)
                        <div class="modal-exam-item p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-white hover:border-gray-200 transition"
                             data-exam-title="{{ strtolower($ae->title) }}"
                             data-exam-class="{{ strtolower($ae->schoolClass?->name ?? '') }}">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div>
                                    <h4 class="font-bold text-xs text-heading flex items-center gap-1.5">
                                        <i data-lucide="clock-3" class="w-3.5 h-3.5 text-navy"></i>
                                        {{ $ae->title }}
                                    </h4>
                                    <div class="text-[11px] text-body mt-0.5">
                                        Class: <strong>{{ $ae->schoolClass?->name ?? 'N/A' }}</strong> &bull; {{ $ae->start_date?->format('M Y') }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold flex-shrink-0 {{ $ae->results_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $ae->results_count }} results
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-body pt-2 border-t border-gray-100 mt-2">
                                <span>Schedule: {{ $ae->start_date?->format($dateFormat) }}</span>
                                <a href="{{ route('examination.results.generate-form', $ae) }}"
                                   class="text-navy hover:underline font-semibold flex items-center gap-0.5">
                                    {{ $ae->results_count > 0 ? 'View / Record More' : 'Generate Results' }} &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6 text-xs text-body bg-gray-50 rounded-xl">
                            No exams registered for this session.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tab 2: Exams of Months Timeline ("exam of months") --}}
            <div id="tab-content-months" class="hidden space-y-4 animate-fadeIn">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 flex items-center justify-between">
                    <span>
                        Monthly distribution of examinations and recorded results for session <strong>{{ $session->name }}</strong>:
                    </span>
                    <span class="font-bold text-blue-800">{{ $analytics['activeMonthsCount'] }} Month(s) Scheduled</span>
                </div>

                <div class="space-y-3">
                    @forelse($analytics['monthsBreakdown'] as $mb)
                        <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm hover:border-blue-400 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-700 font-bold">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-heading">{{ $mb['full_label'] }}</h4>
                                        <p class="text-[11px] text-body">
                                            {{ $mb['exam_count'] }} Exam{{ $mb['exam_count'] == 1 ? '' : 's' }} &bull; {{ $mb['results_count'] }} Result{{ $mb['results_count'] == 1 ? '' : 's' }} Recorded
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('examination.results.index', ['session_id' => $session->id, 'month' => $mb['key']]) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition">
                                        <i data-lucide="filter" class="w-3 h-3"></i> Filter Table to {{ $mb['label'] }}
                                    </a>
                                </div>
                            </div>

                            {{-- Month Exam List --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 pt-3">
                                @foreach($mb['exams'] as $me)
                                    <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                                        <div class="min-w-0 pr-2">
                                            <div class="font-bold text-heading truncate">{{ $me['title'] }}</div>
                                            <div class="text-[11px] text-body">{{ $me['class_name'] }} &bull; {{ $me['start_date'] }}</div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold flex-shrink-0 {{ $me['results_count'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $me['results_count'] }} results
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-body bg-gray-50 rounded-xl">
                            No monthly examination records found for this session.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tab 3: Class-Wise Breakdown --}}
            <div id="tab-content-classes" class="hidden space-y-4 animate-fadeIn">
                <p class="text-xs text-body">
                    Exam results recorded status across classes in <strong>{{ $session->name }}</strong>:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($analytics['classBreakdown'] as $cb)
                        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-white hover:border-gray-200 transition">
                            <div class="flex items-center justify-between mb-1.5">
                                <h4 class="font-bold text-xs text-heading flex items-center gap-1.5">
                                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-navy"></i>
                                    {{ $cb['name'] }}
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $cb['results_count'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $cb['results_count'] }} result(s)
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden mb-1.5">
                                <div class="bg-navy h-1.5 rounded-full transition-all duration-500" style="width: {{ $cb['coverage_pct'] }}%;"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-body">
                                <span>{{ $cb['exam_count'] }} exam(s) &bull; {{ $cb['coverage_pct'] }}% exams evaluated</span>
                                @if($cb['exam_count'] > 0)
                                    <a href="{{ route('examination.results.index', ['session_id' => $session->id, 'class_id' => $cb['id']]) }}"
                                       class="text-navy hover:underline font-semibold flex items-center gap-0.5">
                                        Filter Table &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6 text-xs text-body bg-gray-50 rounded-xl">
                            No classes defined for this academic session.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <span class="text-xs text-body">
                EdOrbit Examination Workflow &bull; Session {{ $session->name }}
            </span>
            <button type="button" onclick="closeResultAnalysisModal()"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-heading text-xs font-semibold transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // ══ Bulk Select JS ══
    function toggleSelectAll(cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = cb.checked;
            r.closest('tr').classList.toggle('bg-red-50/50', cb.checked);
        });
        updateBulkToolbar();
    }
    
    function updateBulkToolbar() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var all     = document.querySelectorAll('.row-checkbox');
        var toolbar = document.getElementById('bulkToolbar');
        var countEl = document.getElementById('selectedCount');
        var sa      = document.getElementById('selectAll');
        
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.closest('tr').classList.toggle('bg-red-50/50', r.checked);
        });
        
        if (checked.length > 0) {
            if(toolbar) toolbar.classList.remove('hidden');
            if(countEl) countEl.textContent = checked.length + ' selected';
        } else {
            if(toolbar) toolbar.classList.add('hidden');
        }
        
        if (sa) {
            sa.indeterminate = checked.length > 0 && checked.length < all.length;
            sa.checked = all.length > 0 && checked.length === all.length;
        }
    }
    
    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = false;
            r.closest('tr').classList.remove('bg-red-50/50');
        });
        var sa = document.getElementById('selectAll');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
        var toolbar = document.getElementById('bulkToolbar');
        if(toolbar) toolbar.classList.add('hidden');
    }
    
    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm('Are you sure you want to clear generated results from the selected ' + checked.length + ' exams?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    // ══ Session Analysis Modal JS ══
    function openResultAnalysisModal(tab) {
        var modal = document.getElementById('resultAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            if (tab) switchAnalysisTab(tab);
        }
    }

    function closeResultAnalysisModal() {
        var modal = document.getElementById('resultAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function switchAnalysisTab(tabName) {
        var tabs = ['exams', 'months', 'classes'];
        tabs.forEach(function(t) {
            var btn = document.getElementById('tab-' + t + '-btn');
            var content = document.getElementById('tab-content-' + t);
            if (btn && content) {
                if (t === tabName) {
                    btn.classList.add('border-navy', 'text-navy', 'font-bold');
                    btn.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-navy', 'text-navy', 'font-bold');
                    btn.classList.add('border-transparent', 'text-gray-500', 'font-semibold');
                    content.classList.add('hidden');
                }
            }
        });
    }

    function filterModalExams(keyword) {
        var kw = keyword.toLowerCase().trim();
        document.querySelectorAll('.modal-exam-item').forEach(function(el) {
            var title = el.getAttribute('data-exam-title') || '';
            var cls = el.getAttribute('data-exam-class') || '';
            var match = title.includes(kw) || cls.includes(kw);
            el.style.display = match ? '' : 'none';
        });
    }

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeResultAnalysisModal();
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<style>
    @keyframes scaleUp {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-scaleUp {
        animation: scaleUp 0.18s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fadeIn {
        animation: fadeIn 0.15s ease-out forwards;
    }
</style>
@endsection