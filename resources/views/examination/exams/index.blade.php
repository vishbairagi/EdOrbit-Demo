@extends('layouts.app')

@section('header', 'Manage Exams')

@section('content')

{{-- ══ Header Breadcrumb & Top Actions ══ --}}
<div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-navy flex items-center justify-center shadow-sm text-white">
            <i data-lucide="clock-3" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-heading leading-tight">{{ $school->name }}</h2>
            <p class="text-xs text-body">
                Academic Session: <span class="font-semibold text-navy">{{ $session->name }}</span> &bull; Examination Module
            </p>
        </div>
    </div>

    {{-- Top Action Buttons --}}
    <div class="flex items-center gap-2 self-stretch sm:self-auto flex-wrap">
        <button type="button" onclick="openExamAnalysisModal('classes')"
                class="inline-flex items-center justify-center px-3.5 py-2 bg-white border border-gray-200 text-heading text-xs font-semibold rounded-lg hover:bg-gray-50 shadow-sm transition-all"
                title="View deep analysis of exams for this session">
            <i data-lucide="bar-chart-2" class="w-4 h-4 mr-1.5 text-navy"></i>
            Session Analytics
        </button>

        <a href="{{ route('examination.exams.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark shadow-sm transition-all">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
            Add New Exam
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

@if(isset($errors) && $errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-r-xl shadow-sm">
        <div class="flex items-start">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 mr-2 flex-shrink-0 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-semibold text-red-800">Please correct the following errors:</h4>
                <ul class="list-disc pl-5 mt-1 text-xs text-red-700 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

{{-- ══ 4 KEY SESSION ANALYSIS CIRCLE / MODEL BOXES ══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Box 1: Total Exams Volume --}}
    <div onclick="openExamAnalysisModal('classes')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-navy/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-navy">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                    <span>Total Exams</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-navy transition-colors">
                    {{ $analytics['totalExams'] }}
                    <span class="text-xs font-semibold text-stone-500">Exams</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        {{ $analytics['totalPapers'] }} Papers
                    </span>
                    <span class="text-stone-400 text-[11px]">in session</span>
                </div>
            </div>

            {{-- Circular Metric Ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-navy transition-all duration-1000 ease-out"
                            stroke-dasharray="100, 100" stroke-width="3.5"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-black text-navy">{{ $analytics['totalExams'] }}</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-navy uppercase tracking-tight mt-1 text-center">Session</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Session: <strong>{{ $session->name }}</strong></span>
            <span class="text-navy group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Class details <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 2: Active vs Inactive Exams --}}
    <div onclick="openExamAnalysisModal('status')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-emerald-700">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    <span>Active Status</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-emerald-700 transition-colors">
                    {{ $analytics['activeExams'] }}
                    <span class="text-xs font-semibold text-stone-500">Active</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $analytics['activeRate'] }}% Active Rate
                    </span>
                    <span class="text-stone-400 text-[11px]">{{ $analytics['inactiveExams'] }} inactive</span>
                </div>
            </div>

            {{-- Circular Progress Ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-emerald-500 transition-all duration-1000 ease-out"
                            stroke-dasharray="{{ $analytics['activeRate'] }}, 100" stroke-width="3.5"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-black text-emerald-700">{{ $analytics['activeRate'] }}%</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-tight mt-1 text-center">Active</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Status distribution</span>
            <span class="text-emerald-600 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                View status <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 3: Class Exam Coverage --}}
    <div onclick="openExamAnalysisModal('classes')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-amber-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-700">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Class Coverage</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-amber-700 transition-colors">
                    {{ $analytics['classCoverageRate'] }}%
                    <span class="text-xs font-semibold text-stone-500">Covered</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        {{ $analytics['classesWithExams'] }} / {{ $analytics['totalClasses'] }} Classes
                    </span>
                </div>
            </div>

            {{-- Circular Progress Ring --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="relative w-16 h-16 flex items-center justify-center">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-stone-100" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-amber-500 transition-all duration-1000 ease-out"
                            stroke-dasharray="{{ $analytics['classCoverageRate'] }}, 100" stroke-width="3.5"
                            stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-black text-amber-700">{{ $analytics['classCoverageRate'] }}%</span>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-amber-700 uppercase tracking-tight mt-1 text-center">Classes</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Class-wise exam count</span>
            <span class="text-amber-700 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Breakdown <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>

    {{-- Box 4: Logistics Activity (Admit Cards & Results) --}}
    <div onclick="openExamAnalysisModal('workflow')"
         class="bg-white rounded-2xl p-5 border border-stone-200 shadow-sm hover:shadow-md hover:border-purple-500/40 transition-all duration-200 cursor-pointer group">
        <div class="flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-700">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Logistics & Results</span>
                </div>
                <h3 class="text-2xl font-black mt-1 text-heading group-hover:text-purple-700 transition-colors">
                    {{ $analytics['totalAdmitCards'] }}
                    <span class="text-xs font-semibold text-stone-500">Admit Cards</span>
                </h3>
                <div class="flex items-center gap-1.5 mt-2 text-xs flex-wrap">
                    <span class="inline-flex items-center gap-1 font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        {{ $analytics['totalResults'] }} Results Logged
                    </span>
                </div>
            </div>

            {{-- Circular Metric Display --}}
            <div class="flex flex-col items-center flex-shrink-0">
                <div class="w-16 h-16 rounded-full bg-purple-50 border-2 border-purple-200 flex items-center justify-center text-purple-700 font-bold">
                    <i data-lucide="file-check-2" class="w-7 h-7 text-purple-600"></i>
                </div>
                <span class="text-[10px] font-bold text-purple-700 uppercase tracking-tight mt-1 text-center">Workflow</span>
            </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
            <span>Workflow metrics</span>
            <span class="text-purple-700 group-hover:underline font-semibold inline-flex items-center gap-0.5">
                Full report <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </span>
        </div>
    </div>
</div>

{{-- ══ Filters and Search Bar ══ --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('examination.exams.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 flex-1">
            {{-- Search Keyword --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-body mb-1">Search Exam:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Exam title, center, class, group..."
                           class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 pr-7 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('examination.exams.index', array_filter(['class_id' => request('class_id'), 'status' => request('status'), 'exam_group_id' => request('exam_group_id'), 'exam_type' => request('exam_type'), 'per_page' => request('per_page')])) }}"
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

            {{-- Status Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Status:</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            {{-- Exam Group Filter --}}
            <div>
                <label class="block text-xs font-medium text-body mb-1">Exam Group:</label>
                <select name="exam_group_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-xs px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                    <option value="">All Groups</option>
                    @foreach($examGroups as $eg)
                        <option value="{{ $eg->id }}" {{ request('exam_group_id') == $eg->id ? 'selected' : '' }}>
                            {{ $eg->name }}
                        </option>
                    @endforeach
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
            @if(request()->hasAny(['search', 'class_id', 'status', 'exam_group_id', 'exam_type', 'per_page']))
                <a href="{{ route('examination.exams.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-100 text-heading text-xs font-medium hover:bg-gray-200 transition">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ══ Bulk Delete Toolbar ══ --}}
<div id="bulkToolbar" class="hidden mb-4 flex items-center justify-between bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-fadeIn">
    <div class="flex items-center gap-2 text-sm font-semibold text-red-800">
        <i data-lucide="check-square" class="w-4 h-4 text-red-600"></i>
        <span id="selectedCount">0 exams selected</span>
    </div>
    <form id="bulkDeleteForm" method="POST" action="{{ route('examination.exams.bulk-destroy') }}" onsubmit="return confirmBulkDelete(event)" class="flex items-center gap-2">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="button" onclick="clearSelection()" class="px-2.5 py-1 text-xs rounded bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
            Cancel
        </button>
        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded bg-red-600 text-white hover:bg-red-700 transition">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            Delete Selected
        </button>
    </form>
</div>

{{-- ══ Exams Table Card ══ --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
    {{-- Pagination Summary Banner --}}
    @if($exams->total() > 0)
        <div class="px-5 py-2.5 bg-gray-50/70 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs text-body">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-navy/10 text-navy font-semibold text-[11px]">
                    Page {{ $exams->currentPage() }} of {{ $exams->lastPage() }}
                </span>
                <span>
                    Showing <strong>{{ $exams->firstItem() }}</strong> to <strong>{{ $exams->lastItem() }}</strong> of <strong>{{ $exams->total() }}</strong> exams
                </span>
            </div>
            @if(request()->hasAny(['search', 'class_id', 'status', 'exam_group_id', 'exam_type']))
                <span class="text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full text-[11px] font-medium">
                    Filtered results
                </span>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px] border-collapse text-xs text-left">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-body uppercase tracking-wider">
                    <th class="py-3 px-3 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                               class="rounded border-gray-300 text-navy focus:ring-navy cursor-pointer" title="Select All">
                    </th>
                    <th class="py-3 px-3 w-12 text-center text-gray-400 font-bold">#</th>
                    <th class="py-3 px-4">Exam Title</th>
                    <th class="py-3 px-4">Class / Sections</th>
                    <th class="py-3 px-4">Exam Center</th>
                    <th class="py-3 px-4">Schedule Dates</th>
                    <th class="py-3 px-4">Exam Group</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4 text-center">Papers</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @forelse($exams as $exam)
                    <tr class="hover:bg-gray-50/70 transition-colors align-top" id="exam-row-{{ $exam->id }}">
                        {{-- Row Checkbox --}}
                        <td class="py-3.5 px-3 text-center">
                            <input type="checkbox" value="{{ $exam->id }}" onchange="updateBulkToolbar()"
                                   class="row-checkbox rounded border-gray-300 text-navy focus:ring-navy cursor-pointer">
                        </td>

                        {{-- Row Index Offset --}}
                        <td class="py-3.5 px-3 text-center text-gray-400 font-semibold text-[11px]">
                            {{ $exams->firstItem() + $loop->index }}
                        </td>

                        {{-- Exam Title --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-navy/10 text-navy flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">
                                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-heading text-sm leading-tight hover:text-navy">
                                        {{ $exam->title }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-body">
                                        <span class="inline-flex items-center gap-1 text-gray-500">
                                            <i data-lucide="id-card" class="w-3 h-3 text-purple-600"></i>
                                            {{ $exam->admit_cards_count }} Admit Cards
                                        </span>
                                        <span>&bull;</span>
                                        <span class="inline-flex items-center gap-1 text-gray-500">
                                            <i data-lucide="award" class="w-3 h-3 text-emerald-600"></i>
                                            {{ $exam->results_count }} Results
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Class / Sections --}}
                        <td class="py-3.5 px-4">
                            <div class="flex flex-col gap-1 items-start">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i data-lucide="graduation-cap" class="w-3 h-3 mr-1"></i>
                                    {{ $exam->schoolClass->name ?? 'Class' }}
                                </span>
                                @if($exam->sections->count() > 0)
                                    <div class="text-[11px] text-body">
                                        Sec: <span class="font-medium text-heading">{{ $exam->sections->pluck('name')->join(', ') }}</span>
                                    </div>
                                @else
                                    <span class="text-[10.5px] text-gray-400">All Sections</span>
                                @endif
                            </div>
                        </td>

                        {{-- Exam Center --}}
                        <td class="py-3.5 px-4 text-heading">
                            <div class="flex items-center gap-1.5 font-medium">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>{{ $exam->exam_center ?: 'Main Hall' }}</span>
                            </div>
                        </td>

                        {{-- Start & End Dates --}}
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex flex-col gap-0.5 text-heading">
                                <div class="flex items-center gap-1 text-xs font-semibold">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>{{ $exam->start_date ? $exam->start_date->format('d M Y') : '—' }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-body pl-4">
                                    <span>to {{ $exam->end_date ? $exam->end_date->format('d M Y') : '—' }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Exam Group --}}
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($exam->examGroup)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                    <i data-lucide="layers" class="w-3 h-3"></i>
                                    {{ $exam->examGroup->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Exam Type --}}
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $exam->exam_type === 'marks' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-stone-100 text-stone-700 border border-stone-200' }}">
                                {{ $exam->exam_type === 'marks' ? 'Marks & Grade' : 'Grade Only' }}
                            </span>
                        </td>

                        {{-- Papers Count --}}
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-full bg-blue-100 text-blue-800 font-bold text-xs">
                                {{ $exam->papers_count }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($exam->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Action Column --}}
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Edit Button --}}
                                <a href="{{ route('examination.exams.edit', $exam) }}"
                                   class="p-1.5 rounded-lg border border-gray-200 text-gray-700 hover:border-navy hover:text-navy hover:bg-navy/5 transition shadow-sm"
                                   title="Edit Exam">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('examination.exams.destroy', $exam) }}"
                                      onsubmit="return confirm('Are you sure you want to delete exam: {{ addslashes($exam->title) }}? All related papers, admit cards, and results will also be deleted.')"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg border border-gray-200 text-red-600 hover:border-red-500 hover:bg-red-50 transition shadow-sm"
                                            title="Delete Exam">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center">
                            <div class="max-w-sm mx-auto flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-gray-400">
                                    <i data-lucide="calendar-x-2" class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-sm font-bold text-heading">No Exams Found</h4>
                                <p class="text-xs text-body mt-1">
                                    @if(request()->hasAny(['search', 'class_id', 'status', 'exam_group_id']))
                                        No exams match the active filters for session <strong>{{ $session->name }}</strong>.
                                    @else
                                        No exams have been created for session <strong>{{ $session->name }}</strong> yet.
                                    @endif
                                </p>
                                <div class="mt-4 flex items-center gap-2">
                                    @if(request()->hasAny(['search', 'class_id', 'status', 'exam_group_id']))
                                        <a href="{{ route('examination.exams.index') }}"
                                           class="inline-flex items-center px-3.5 py-2 bg-gray-100 text-heading text-xs font-semibold rounded-lg hover:bg-gray-200 transition">
                                            Clear Filters
                                        </a>
                                    @endif
                                    <a href="{{ route('examination.exams.create') }}"
                                       class="inline-flex items-center px-4 py-2 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark transition shadow-sm">
                                        <i data-lucide="plus" class="w-3.5 h-3.5 mr-1.5"></i>
                                        Create New Exam
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer Bar --}}
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
</div>

{{-- ═════════════════════════════════════════════════════════════════ --}}
{{-- INTERACTIVE SESSION ANALYSIS MODAL (examAnalysisModal)            --}}
{{-- ═════════════════════════════════════════════════════════════════ --}}
<div id="examAnalysisModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden animate-scaleUp">
        {{-- Modal Header --}}
        <div class="px-6 py-4 bg-navy text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white leading-tight">Session Examination Analytics</h3>
                    <p class="text-xs text-white/70">Session: {{ $session->name }} &bull; {{ $school->name }}</p>
                </div>
            </div>
            <button type="button" onclick="closeExamAnalysisModal()" class="text-white/70 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Modal Navigation Tabs --}}
        <div class="flex border-b border-gray-200 bg-gray-50 px-6 pt-3 gap-3">
            <button type="button" onclick="switchAnalysisTab('classes')" id="tab-classes-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-bold border-b-2 border-navy text-navy flex items-center gap-1.5 transition">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                Class-Wise Breakdown ({{ count($analytics['classBreakdown']) }})
            </button>
            <button type="button" onclick="switchAnalysisTab('status')" id="tab-status-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-semibold text-gray-500 hover:text-navy border-b-2 border-transparent flex items-center gap-1.5 transition">
                <i data-lucide="activity" class="w-4 h-4"></i>
                Status & Groups
            </button>
            <button type="button" onclick="switchAnalysisTab('workflow')" id="tab-workflow-btn"
                    class="tab-btn pb-2.5 px-3 text-xs font-semibold text-gray-500 hover:text-navy border-b-2 border-transparent flex items-center gap-1.5 transition">
                <i data-lucide="layers" class="w-4 h-4"></i>
                Workflow & Logistics
            </button>
        </div>

        {{-- Modal Content Area --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            {{-- Tab 1: Class-Wise Breakdown --}}
            <div id="tab-content-classes" class="space-y-4 animate-fadeIn">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-body">
                        Distribution of scheduled examinations across each class for <strong>{{ $session->name }}</strong>:
                    </p>
                    <input type="text" id="modalClassSearch" oninput="filterModalClasses(this.value)" placeholder="Search class..."
                           class="w-44 px-2.5 py-1 rounded-lg border border-gray-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-navy">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="modalClassGrid">
                    @foreach($analytics['classBreakdown'] as $cb)
                        <div class="modal-class-item p-3.5 rounded-xl border border-gray-100 bg-gray-50/60 hover:bg-white hover:border-gray-200 transition"
                             data-class-name="{{ strtolower($cb['name']) }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <h4 class="font-bold text-xs text-heading flex items-center gap-1.5">
                                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-navy"></i>
                                    {{ $cb['name'] }}
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $cb['exam_count'] > 0 ? 'bg-navy/10 text-navy' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $cb['exam_count'] }} exam(s)
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden mb-1.5">
                                <div class="bg-navy h-1.5 rounded-full transition-all duration-500" style="width: {{ $cb['percentage'] }}%;"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-body">
                                <span>{{ $cb['active_count'] }} Active &bull; {{ $cb['percentage'] }}% of total</span>
                                @if($cb['exam_count'] > 0)
                                    <a href="{{ route('examination.exams.index', ['class_id' => $cb['id']]) }}"
                                       class="text-navy hover:underline font-semibold flex items-center gap-0.5">
                                        Filter Table &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tab 2: Status & Exam Groups --}}
            <div id="tab-content-status" class="hidden space-y-4 animate-fadeIn">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Active Exams Card --}}
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                                Active / Scheduled Exams
                            </span>
                            <span class="text-xl font-black text-emerald-700">{{ $analytics['activeExams'] }}</span>
                        </div>
                        <p class="text-xs text-emerald-700 leading-relaxed">
                            {{ $analytics['activeRate'] }}% of total session examinations are actively scheduled and accessible to staff and students.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('examination.exams.index', ['status' => 'active']) }}"
                               class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 hover:underline">
                                Filter by Active Exams &rarr;
                            </a>
                        </div>
                    </div>

                    {{-- Inactive / Draft Exams Card --}}
                    <div class="p-4 rounded-xl bg-gray-100 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                <i data-lucide="pause-circle" class="w-4 h-4 text-gray-500"></i>
                                Inactive / Draft Exams
                            </span>
                            <span class="text-xl font-black text-gray-800">{{ $analytics['inactiveExams'] }}</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Draft examinations that are currently unpublished or disabled from public examination schedules.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('examination.exams.index', ['status' => 'inactive']) }}"
                               class="inline-flex items-center gap-1 text-xs font-bold text-gray-800 hover:underline">
                                Filter by Inactive Exams &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Exam Groups Distribution --}}
                <div class="pt-2">
                    <h4 class="text-xs font-bold text-heading uppercase tracking-wider mb-2">Exam Groups Breakdown</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @forelse($analytics['groupBreakdown'] as $gb)
                            <div class="p-3 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center justify-between">
                                <div>
                                    <h5 class="text-xs font-bold text-heading">{{ $gb['name'] }}</h5>
                                    <p class="text-[11px] text-body">Exam Group</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $gb['count'] }}
                                </span>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-4 text-xs text-body bg-gray-50 rounded-xl">
                                No Exam Groups configured for this session yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Tab 3: Workflow & Logistics --}}
            <div id="tab-content-workflow" class="hidden space-y-4 animate-fadeIn">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mx-auto mb-2 font-bold">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-2xl font-black text-blue-900">{{ $analytics['totalPapers'] }}</h4>
                        <p class="text-xs font-semibold text-blue-700 mt-1">Exam Papers</p>
                        <p class="text-[11px] text-blue-600 mt-0.5">Subject assessments scheduled</p>
                    </div>

                    <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 text-center">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center mx-auto mb-2 font-bold">
                            <i data-lucide="id-card" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-2xl font-black text-purple-900">{{ $analytics['totalAdmitCards'] }}</h4>
                        <p class="text-xs font-semibold text-purple-700 mt-1">Admit Cards</p>
                        <p class="text-[11px] text-purple-600 mt-0.5">Generated & assigned to students</p>
                    </div>

                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 font-bold">
                            <i data-lucide="award" class="w-5 h-5"></i>
                        </div>
                        <h4 class="text-2xl font-black text-emerald-900">{{ $analytics['totalResults'] }}</h4>
                        <p class="text-xs font-semibold text-emerald-700 mt-1">Student Results</p>
                        <p class="text-[11px] text-emerald-600 mt-0.5">Evaluated and marks recorded</p>
                    </div>
                </div>

                {{-- Action Shortcuts --}}
                <div class="p-4 rounded-xl bg-stone-50 border border-stone-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                        <h5 class="font-bold text-heading">Looking for quick examination actions?</h5>
                        <p class="text-body text-[11.5px]">Manage admit cards, print roll numbers, or publish term examination results.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('examination.admit-cards.index') }}"
                           class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-heading font-semibold hover:bg-gray-100 transition shadow-sm">
                            Manage Admit Cards
                        </a>
                        <a href="{{ route('examination.results.index') }}"
                           class="px-3 py-1.5 rounded-lg bg-navy text-white font-semibold hover:bg-primary-dark transition shadow-sm">
                            View Results
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-body">
            <span>Session: <strong>{{ $session->name }}</strong></span>
            <button type="button" onclick="closeExamAnalysisModal()"
                    class="px-4 py-2 rounded-lg bg-navy text-white text-xs font-semibold hover:bg-primary-dark transition shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ══ JavaScript Handlers ══ --}}
<script>
    // Modal Control: Exam Analysis Modal
    function openExamAnalysisModal(initialTab = 'classes') {
        document.getElementById('examAnalysisModal').classList.remove('hidden');
        switchAnalysisTab(initialTab);
        lucide.createIcons();
    }

    function closeExamAnalysisModal() {
        document.getElementById('examAnalysisModal').classList.add('hidden');
    }

    function switchAnalysisTab(tabName) {
        // Hide all contents
        document.getElementById('tab-content-classes').classList.add('hidden');
        document.getElementById('tab-content-status').classList.add('hidden');
        document.getElementById('tab-content-workflow').classList.add('hidden');

        // Reset button styles
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-navy', 'text-navy', 'font-bold');
            b.classList.add('border-transparent', 'text-gray-500', 'font-semibold');
        });

        // Activate selected
        const activeContent = document.getElementById('tab-content-' + tabName);
        const activeBtn = document.getElementById('tab-' + tabName + '-btn');
        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.add('border-navy', 'text-navy', 'font-bold');
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
        }
        lucide.createIcons();
    }

    function filterModalClasses(query) {
        query = query.toLowerCase().trim();
        document.querySelectorAll('.modal-class-item').forEach(el => {
            const name = el.getAttribute('data-class-name') || '';
            el.style.display = name.includes(query) ? '' : 'none';
        });
    }

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
            if(countEl) countEl.textContent = checked.length + ' exam(s) selected';
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

    function confirmBulkDelete(e) {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm('Are you sure you want to permanently delete ' + checked.length + ' selected exam(s)? All related papers and results will be removed.')) {
            if (e) e.preventDefault();
            return false;
        }
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
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
</style>

@endsection