@extends('layouts.app')

@section('header', 'Assign Subject')

@section('content')

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Subject Assignments</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('academic.assign-subjects.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('academic.assign-subjects.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-1"></i> Sample CSV
                </a>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload CSV File <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" accept=".csv" required class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy focus:border-navy">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-navy hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">Import Data</button>
            </div>
        </form>
    </div>
</div>

{{-- Top Header Section (Matching /academic/class-teachers, class-sections, subjects, mediums) --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Assign Subjects</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Subjects: {{ $subjects->total() }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('academic.assign-subjects-bulk.index') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
            <i data-lucide="users" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Assign in Bulk
        </a>
        <button type="button" onclick="openAssignAnalysisModal('teachers')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="pie-chart" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> View Analysis
        </button>
        <a href="{{ route('academic.assign-subjects.export') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Export CSV
        </a>
        <button type="button" onclick="openImportModal()"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Import CSV
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs font-medium flex items-center gap-2"
     style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
    {{ session('error') }}
</div>
@endif

{{-- Search & Class Filter Bar with Pagination Per-Page & Status --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 mb-3.5">
    <form method="GET" action="{{ route('academic.assign-subjects.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            
            {{-- Class Dropdown --}}
            <div class="w-full md:w-56">
                <label class="block text-xs font-semibold text-heading mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Assignment Status Filter --}}
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-heading mb-1">Assignment Status:</label>
                <select name="assignment_status" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Subjects</option>
                    <option value="assigned" {{ request('assignment_status') === 'assigned' ? 'selected' : '' }}>Assigned (Has Teachers)</option>
                    <option value="unassigned" {{ request('assignment_status') === 'unassigned' ? 'selected' : '' }}>Unassigned (No Teachers)</option>
                    <option value="cotaught" {{ request('assignment_status') === 'cotaught' ? 'selected' : '' }}>Co-Taught (2+ Teachers)</option>
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-semibold text-heading mb-1">Search Subject / Teacher:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject name, code, or teacher..."
                           class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.assign-subjects.index', array_filter(['class_id' => request('class_id'), 'assignment_status' => request('assignment_status'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Per-Page Dropdown (Pagination Functionality) --}}
            <div class="w-full md:w-32">
                <label class="block text-xs font-semibold text-heading mb-1">Per Page:</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white cursor-pointer"
                        title="Records per page">
                    <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / page</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 / page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
                </select>
            </div>

            <div class="flex items-end mt-auto gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:bg-primary-dark transition bg-navy shadow-2xs">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request('class_id') || request('assignment_status') || request('search') || (request('per_page') && request('per_page') != 10))
                    <a href="{{ route('academic.assign-subjects.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-medium text-red-600 hover:bg-gray-100 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>


{{-- Empty State --}}
@if($subjects->isEmpty())
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="book-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No subjects found matching your criteria.</p>
        <p class="text-sm text-body mt-1">Try adjusting your filters, search keywords, or add subjects first.</p>
        @if(request()->anyFilled(['class_id', 'assignment_status', 'search']))
            <a href="{{ route('academic.assign-subjects.index') }}"
               class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Clear Filters
            </a>
        @endif
    </div>
@else
    {{-- ══ Bulk Delete Toolbar ══ --}}
    <div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
        <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
        <form id="bulkDeleteForm" method="POST" action="{{ route('academic.assign-subjects.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            <div id="bulkIdsContainer"></div>
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear Assigned Teachers
            </button>
        </form>
        <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
    </div>

    {{-- Main Table Container --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-4 py-2.5 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-gray-600">
                    Showing: <strong class="text-gray-900 font-bold">{{ $subjects->firstItem() ?? 0 }} - {{ $subjects->lastItem() ?? 0 }}</strong> of <strong class="text-gray-900 font-bold">{{ $subjects->total() }}</strong> subjects
                </span>
                <span class="text-xs text-gray-300">|</span>
                <span class="text-xs text-gray-600 font-medium">Session: <strong class="text-gray-900">{{ $session->name }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                @if(request('class_id'))
                    @php $activeFilteredClass = $classes->firstWhere('id', request('class_id')); @endphp
                    <span class="text-xs font-medium text-gray-800 bg-white px-2.5 py-0.5 rounded-md border border-gray-300">
                        Class: {{ $activeFilteredClass?->name ?? 'ID '.request('class_id') }}
                    </span>
                @endif
                @if(request('assignment_status'))
                    <span class="text-xs font-medium text-gray-800 bg-white px-2.5 py-0.5 rounded-md border border-gray-300 capitalize">
                        Status: {{ request('assignment_status') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead>
                    <tr class="bg-navy">
                        <th class="px-3 py-2.5 w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                        </th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Name</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Code</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Type</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Assigned Teachers</th>
                        <th class="px-4 py-2.5 text-right text-white font-bold text-xs uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($subjects as $subject)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-3 py-2.5 text-center w-10">
                                <input type="checkbox" value="{{ $subject->id }}" onchange="updateBulkToolbar()"
                                    class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="font-bold text-xs text-gray-950">{{ $subject->name }}</div>
                                @if($subject->subjectType)
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $subject->subjectType->name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($subject->code)
                                    <span class="text-xs font-mono font-medium text-gray-800">{{ $subject->code }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs font-bold text-gray-900">
                                {{ $subject->schoolClass?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-xs">
                                <span class="text-gray-700 font-medium">
                                    {{ ($subject->type === 'practical' || strtolower($subject->subjectType?->name ?? '') === 'practical') ? 'Practical / Lab' : 'Theory' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($subject->teachers->count() > 0)
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('academic.assign-subjects.teachers', $subject) }}"
                                           class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-200 transition-colors">
                                            <i data-lucide="{{ $subject->teachers->count() > 1 ? 'users' : 'user-check' }}" class="w-3.5 h-3.5 text-gray-600"></i>
                                            <span>{{ $subject->teachers->count() }} {{ Str::plural('Teacher', $subject->teachers->count()) }}</span>
                                        </a>
                                        <div class="text-xs text-gray-600 flex flex-wrap gap-1 items-center">
                                            @foreach($subject->teachers->take(2) as $t)
                                                <span class="text-[11px] font-medium text-gray-700">
                                                    {{ $t->name }}@if(!$loop->last),@endif
                                                </span>
                                            @endforeach
                                            @if($subject->teachers->count() > 2)
                                                <span class="text-[10px] text-gray-400 font-semibold">+{{ $subject->teachers->count() - 2 }} more</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('academic.assign-subjects.teachers', $subject) }}"
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium text-gray-500 hover:text-gray-800 hover:underline transition-colors">
                                        <i data-lucide="user-plus" class="w-3.5 h-3.5 text-gray-400"></i>
                                        <span>No Teacher Assigned</span>
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <a href="{{ route('academic.assign-subjects.teachers', $subject) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                   title="Manage Teachers">
                                    <i data-lucide="user-cog" class="w-3.5 h-3.5 text-gray-600"></i>
                                    <span>Manage Teachers</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Links Bar --}}
        @if($subjects->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-white">
                {{ $subjects->links() }}
            </div>
        @else
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Showing <strong class="text-gray-700 font-semibold">{{ $subjects->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $subjects->total() }}</strong> subjects</span>
                <span class="text-gray-400">Page 1 of 1</span>
            </div>
        @endif
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════════
     INTERACTIVE DRILLDOWN MODAL: SESSION SUBJECT & TEACHER ASSIGNMENT ANALYSIS
     ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="assignAnalysisModal" class="hidden fixed inset-0 z-[120] flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-5">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-stone-200">
        
        {{-- Modal Top Banner Header --}}
        <div class="bg-navy text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold leading-tight">Session Subject & Faculty Assignment Analysis</h3>
                    <p class="text-xs text-white/70 mt-0.5 flex items-center gap-2">
                        <span>Session: <strong class="text-white">{{ $session->name }}</strong></span>
                        <span>&bull;</span>
                        <span>School: <strong class="text-white">{{ $school->name }}</strong></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <span class="text-amber-300">{{ $analytics['totalSubjects'] }} Subjects</span>
                    <span class="text-white/40">|</span>
                    <span>{{ $analytics['totalTeachers'] }} Faculty</span>
                    <span class="text-white/40">|</span>
                    <span class="text-emerald-300">{{ $analytics['subjectAssignmentRate'] }}% Covered</span>
                </div>
                <button type="button" onclick="closeAssignAnalysisModal()"
                        class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Modal Interactive Tabs & Quick Search Bar --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button type="button" id="analysisBtn_teachers" onclick="switchAssignTab('teachers')"
                        class="assign-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-navy text-white shadow-sm">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Particular Teacher Assign Subjects</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($analytics['teacherBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_subjects" onclick="switchAssignTab('subjects')"
                        class="assign-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                    <span>Subject Assign Teachers</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-stone-200 text-stone-700">{{ count($analytics['subjectBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_classes" onclick="switchAssignTab('classes')"
                        class="assign-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Class Coverage Matrix</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-stone-200 text-stone-700">{{ count($analytics['classBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_workload" onclick="switchAssignTab('workload')"
                        class="assign-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="gauge" class="w-3.5 h-3.5"></i>
                    <span>Workload Distribution</span>
                </button>
            </div>

            {{-- Client-side quick search inside modal --}}
            <div class="relative w-full md:w-64">
                <input type="text" id="modalSearchInput" oninput="filterModalSearch(this.value)"
                       placeholder="Filter by teacher or subject..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy">
                <i data-lucide="search" class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
            </div>
        </div>

        {{-- Modal Tab Content Scroll Area --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-4" style="background: #faf7f2;">

            {{-- ── TAB 1: PARTICULAR TEACHER ASSIGN SUBJECTS ── --}}
            <div id="assignTab_teachers" class="assign-tab-pane space-y-4">
                <div class="bg-blue-50/70 border border-blue-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-blue-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-blue-700 flex-shrink-0"></i>
                        <span>Showing teaching staff of this school and the exact subjects assigned to each teacher in session <strong>{{ $session->name }}</strong>.</span>
                    </div>
                    <div class="flex items-center gap-2 font-semibold">
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ $analytics['assignedTeachers'] }} Active</span>
                        <span class="px-2 py-0.5 rounded bg-stone-200 text-stone-700">{{ $analytics['unassignedTeachers'] }} Unallocated</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="modalTeachersGrid">
                    @forelse($analytics['teacherBreakdown'] as $t)
                        <div class="modal-teacher-card bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all"
                             data-teacher-name="{{ strtolower($t['name']) }}"
                             data-teacher-emp="{{ strtolower($t['employee_id'] ?? '') }}"
                             data-teacher-subjects="{{ strtolower($t['subjects']->pluck('name')->implode(' ')) }}">
                            
                            {{-- Teacher Header --}}
                            <div class="flex items-start justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-sm"
                                         style="background: {{ $t['count'] > 0 ? '#4F7D5A' : '#9e8f84' }};">
                                        {{ strtoupper(substr($t['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading leading-tight">{{ $t['name'] }}</h4>
                                        <div class="flex items-center gap-2 text-[11px] text-stone-500 mt-0.5">
                                            @if($t['employee_id'])
                                                <span class="font-mono font-semibold">{{ $t['employee_id'] }}</span>
                                                <span>&bull;</span>
                                            @endif
                                            <span>{{ $t['designation'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($t['count'] === 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-stone-100 text-stone-600 border border-stone-200">
                                            0 Subjects Assigned
                                        </span>
                                    @elseif($t['count'] <= 2)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            {{ $t['count'] }} {{ Str::plural('Subject', $t['count']) }} (Light)
                                        </span>
                                    @elseif($t['count'] <= 5)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $t['count'] }} {{ Str::plural('Subject', $t['count']) }} (Balanced)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            {{ $t['count'] }} {{ Str::plural('Subject', $t['count']) }} (Heavy)
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Assigned Subjects List --}}
                            <div class="mt-3">
                                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider block mb-1.5">
                                    Assigned Subjects ({{ $t['count'] }}):
                                </span>
                                @if($t['count'] > 0)
                                    <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto pr-1">
                                        @foreach($t['subjects'] as $sItem)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-stone-50 text-stone-800 border border-stone-200 hover:border-navy/40 transition">
                                                <i data-lucide="book-open" class="w-3 h-3 text-navy"></i>
                                                <span>{{ $sItem['name'] }}</span>
                                                <span class="text-[10px] text-stone-500 font-normal">({{ $sItem['class_name'] }})</span>
                                                @if($sItem['code'])
                                                    <span class="text-[9px] px-1 py-0.2 rounded bg-stone-200 font-mono text-stone-700">{{ $sItem['code'] }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-stone-50 rounded-lg p-2.5 text-center text-xs text-stone-400 italic border border-dashed border-stone-200">
                                        No subjects assigned in this academic session yet.
                                    </div>
                                @endif
                            </div>

                            {{-- Teacher Details Footer --}}
                            <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                                <span>Phone: <strong class="text-stone-700">{{ $t['phone'] ?: 'N/A' }}</strong></span>
                                <span class="text-stone-400 text-[10px]">Status: {{ $t['status'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No active teachers found for this school.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 2: SUBJECT ASSIGN TEACHERS ── --}}
            <div id="assignTab_subjects" class="assign-tab-pane hidden space-y-4">
                <div class="bg-emerald-50/70 border border-emerald-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-emerald-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-emerald-700 flex-shrink-0"></i>
                        <span>Showing curriculum subjects and which teachers are assigned to teach each subject in session <strong>{{ $session->name }}</strong>.</span>
                    </div>
                    <div class="flex items-center gap-2 font-semibold">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">{{ $analytics['assignedSubjects'] }} Covered</span>
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800">{{ $analytics['unassignedSubjects'] }} Missing Faculty</span>
                    </div>
                </div>

                {{-- Class Filter Pills --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                    <button type="button" onclick="filterModalSubjectsByClass('all')"
                            class="modal-class-pill px-3 py-1 rounded-lg text-xs font-bold transition bg-navy text-white shadow-sm" data-class="all">
                        All Classes ({{ $analytics['totalSubjects'] }})
                    </button>
                    @foreach($analytics['classBreakdown'] as $c)
                        <button type="button" onclick="filterModalSubjectsByClass('{{ $c['id'] }}')"
                                class="modal-class-pill px-3 py-1 rounded-lg text-xs font-semibold transition bg-white text-stone-700 border border-stone-200 hover:border-navy"
                                data-class="{{ $c['id'] }}">
                            {{ $c['name'] }} ({{ $c['total_subjects'] }})
                        </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="modalSubjectsGrid">
                    @forelse($analytics['subjectBreakdown'] as $s)
                        <div class="modal-subject-card bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all"
                             data-subject-id="{{ $s['id'] }}"
                             data-class-id="{{ $s['class_id'] }}"
                             data-subject-name="{{ strtolower($s['name']) }}"
                             data-subject-code="{{ strtolower($s['code'] ?? '') }}"
                             data-teachers="{{ strtolower($s['teachers']->pluck('name')->implode(' ')) }}">
                            
                            {{-- Subject Header --}}
                            <div class="flex items-start justify-between pb-3 border-b border-stone-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-sm text-heading leading-tight">{{ $s['name'] }}</h4>
                                        @if($s['code'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-stone-100 text-stone-700 border border-stone-200">
                                                {{ $s['code'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-stone-500 mt-1">
                                        <span class="font-semibold text-stone-700"><i data-lucide="graduation-cap" class="w-3 h-3 inline mr-0.5"></i> {{ $s['class_name'] }}</span>
                                        <span>&bull;</span>
                                        <span class="capitalize">{{ $s['type'] ?? 'Theory' }}</span>
                                    </div>
                                </div>
                                <div>
                                    @if($s['teacher_count'] === 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                            No Teacher
                                        </span>
                                    @elseif($s['teacher_count'] === 1)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            1 Teacher Assigned
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Co-Taught ({{ $s['teacher_count'] }})
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Assigned Teachers List --}}
                            <div class="mt-3">
                                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider block mb-1.5">
                                    Assigned Faculty ({{ $s['teacher_count'] }}):
                                </span>
                                @if($s['teacher_count'] > 0)
                                    <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                                        @foreach($s['teachers'] as $tItem)
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-stone-50 border border-stone-200 text-xs">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-navy text-white flex items-center justify-center text-[10px] font-bold">
                                                        {{ strtoupper(substr($tItem['name'], 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-stone-800">{{ $tItem['name'] }}</span>
                                                        <span class="text-[10px] text-stone-400 block">{{ $tItem['designation'] }} &bull; {{ $tItem['employee_id'] }}</span>
                                                    </div>
                                                </div>
                                                @if($tItem['phone'])
                                                    <span class="text-[10px] text-stone-500 font-mono">{{ $tItem['phone'] }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-amber-50 rounded-lg p-3 text-center text-xs text-amber-800 border border-amber-200 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5 font-medium">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                                            Subject is currently unassigned
                                        </span>
                                        <a href="{{ route('academic.assign-subjects.teachers', $s['id']) }}"
                                           class="px-2.5 py-1 rounded bg-amber-600 text-white font-semibold text-[11px] hover:bg-amber-700 transition shadow-sm">
                                            + Assign Teacher
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Subject Footer --}}
                            <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[11px] text-stone-500">
                                <span>Subject ID: <strong>#{{ $s['id'] }}</strong></span>
                                <a href="{{ route('academic.assign-subjects.teachers', $s['id']) }}"
                                   class="text-navy hover:underline font-semibold inline-flex items-center gap-0.5">
                                    Manage assignments <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No subjects found for this session.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 3: CLASS COVERAGE MATRIX ── --}}
            <div id="assignTab_classes" class="assign-tab-pane hidden space-y-4">
                <div class="bg-purple-50/70 border border-purple-200/70 rounded-xl p-3.5 flex items-center justify-between text-xs text-purple-900">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-purple-700 flex-shrink-0"></i>
                        <span>Class-by-class audit showing curriculum assignment completion across all academic classes.</span>
                    </div>
                    <span class="font-bold px-2 py-0.5 rounded bg-purple-100 text-purple-800">{{ count($analytics['classBreakdown']) }} Classes</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['classBreakdown'] as $c)
                        <div class="bg-white rounded-xl border border-stone-200 p-4 shadow-sm hover:shadow transition-all">
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-navy text-white flex items-center justify-center text-xs font-bold">
                                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $c['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">{{ $c['total_subjects'] }} Total Subjects</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black {{ $c['coverage_rate'] === 100 ? 'text-emerald-700' : ($c['coverage_rate'] > 0 ? 'text-amber-700' : 'text-stone-400') }}">
                                        {{ $c['coverage_rate'] }}%
                                    </span>
                                    <span class="text-[10px] text-stone-400 block font-semibold">Covered</span>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full bg-stone-100 rounded-full h-2.5 my-3 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-700 {{ $c['coverage_rate'] === 100 ? 'bg-emerald-500' : ($c['coverage_rate'] > 0 ? 'bg-amber-500' : 'bg-stone-300') }}"
                                     style="width: {{ $c['coverage_rate'] }}%;"></div>
                            </div>

                            <div class="flex items-center justify-between text-xs text-stone-600 mb-3">
                                <span>Assigned: <strong class="text-emerald-700">{{ $c['assigned_subjects'] }}</strong></span>
                                <span>Unassigned: <strong class="{{ $c['unassigned_subjects'] > 0 ? 'text-amber-700' : 'text-stone-500' }}">{{ $c['unassigned_subjects'] }}</strong></span>
                            </div>

                            {{-- Subjects preview --}}
                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-xs">
                                <span class="text-stone-500 text-[11px]">Subjects in class:</span>
                                <button type="button" onclick="filterMainTableClass({{ $c['id'] }})"
                                        class="text-navy hover:underline font-semibold inline-flex items-center gap-1">
                                    View in Main Table <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No classes found for this session.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 4: TEACHER WORKLOAD DISTRIBUTION ── --}}
            <div id="assignTab_workload" class="assign-tab-pane hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-stone-500 uppercase">Unallocated</span>
                        <h4 class="text-2xl font-black text-stone-700 mt-1">{{ $analytics['workloadDistribution']['unassigned'] }}</h4>
                        <span class="text-[11px] text-stone-400">0 Subjects assigned</span>
                    </div>
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-sky-700 uppercase">Light Load</span>
                        <h4 class="text-2xl font-black text-sky-700 mt-1">{{ $analytics['workloadDistribution']['light'] }}</h4>
                        <span class="text-[11px] text-stone-400">1 - 2 Subjects assigned</span>
                    </div>
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-emerald-700 uppercase">Balanced Load</span>
                        <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $analytics['workloadDistribution']['balanced'] }}</h4>
                        <span class="text-[11px] text-stone-400">3 - 5 Subjects assigned</span>
                    </div>
                    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
                        <span class="text-xs font-bold text-purple-700 uppercase">Heavy Load</span>
                        <h4 class="text-2xl font-black text-purple-700 mt-1">{{ $analytics['workloadDistribution']['heavy'] }}</h4>
                        <span class="text-[11px] text-stone-400">6+ Subjects assigned</span>
                    </div>
                </div>

                {{-- Top Faculty Allocations Table --}}
                <div class="bg-white rounded-xl border border-stone-200 overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-stone-100 font-bold text-sm text-heading flex items-center justify-between" style="background:#F5F1EB;">
                        <span>Faculty Subject Allocations Leaderboard</span>
                        <span class="text-xs font-semibold text-stone-500">Total Allocations: {{ $analytics['totalPairings'] }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-stone-50 text-stone-600 font-semibold uppercase tracking-wider border-b border-stone-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Teacher</th>
                                    <th class="px-4 py-2.5 text-left">Employee ID</th>
                                    <th class="px-4 py-2.5 text-left">Designation</th>
                                    <th class="px-4 py-2.5 text-center">Assigned Subjects</th>
                                    <th class="px-4 py-2.5 text-left">Load Status</th>
                                    <th class="px-4 py-2.5 text-right">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                @forelse($analytics['teacherBreakdown'] as $tb)
                                    <tr class="hover:bg-stone-50 transition">
                                        <td class="px-4 py-2.5 font-bold text-heading flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-navy text-white text-[9px] flex items-center justify-center font-bold">
                                                {{ strtoupper(substr($tb['name'], 0, 1)) }}
                                            </div>
                                            {{ $tb['name'] }}
                                        </td>
                                        <td class="px-4 py-2.5 font-mono text-stone-600">{{ $tb['employee_id'] ?: '—' }}</td>
                                        <td class="px-4 py-2.5 text-stone-600">{{ $tb['designation'] }}</td>
                                        <td class="px-4 py-2.5 text-center font-bold text-sm {{ $tb['count'] > 0 ? 'text-navy' : 'text-stone-400' }}">
                                            {{ $tb['count'] }}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tb['count'] === 0 ? 'bg-stone-100 text-stone-500' : ($tb['count'] <= 2 ? 'bg-sky-50 text-sky-700' : ($tb['count'] <= 5 ? 'bg-emerald-50 text-emerald-700' : 'bg-purple-50 text-purple-700')) }}">
                                                {{ $tb['workload_status'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <button type="button" onclick="switchAssignTab('teachers'); document.getElementById('modalSearchInput').value = '{{ addslashes($tb['name']) }}'; filterModalSearch('{{ addslashes($tb['name']) }}');"
                                                    class="text-navy hover:underline font-semibold">
                                                View Subjects
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-stone-400">No staff found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // ── Import Modal Controls ──
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }
    document.getElementById('importModal')?.addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

    // ── Bulk Select JS ──
    function toggleSelectAll(cb) {
        document.querySelectorAll('.row-checkbox').forEach(function(r) {
            r.checked = cb.checked;
            r.closest('tr').classList.toggle('bg-red-50', cb.checked);
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
            r.closest('tr').classList.toggle('bg-red-50', r.checked);
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
            r.closest('tr').classList.remove('bg-red-50');
        });
        var sa = document.getElementById('selectAll');
        if (sa) { sa.checked = false; sa.indeterminate = false; }
        var toolbar = document.getElementById('bulkToolbar');
        if(toolbar) toolbar.classList.add('hidden');
    }
    
    function confirmBulkDelete() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return false;
        if (!confirm('Are you sure you want to clear assigned teachers from the ' + checked.length + ' selected subject(s)?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }

    // ── Interactive Assignment Analysis Modal Controller ──
    function openAssignAnalysisModal(tab = 'teachers') {
        const modal = document.getElementById('assignAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchAssignTab(tab);
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeAssignAnalysisModal() {
        const modal = document.getElementById('assignAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function switchAssignTab(tabName) {
        document.querySelectorAll('.assign-tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.assign-tab-btn').forEach(btn => {
            btn.classList.remove('bg-navy', 'text-white', 'shadow-sm');
            btn.classList.add('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
        });

        const targetPane = document.getElementById('assignTab_' + tabName);
        const targetBtn = document.getElementById('analysisBtn_' + tabName);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
            targetBtn.classList.add('bg-navy', 'text-white', 'shadow-sm');
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function filterModalSearch(query) {
        query = (query || '').toLowerCase().trim();

        // 1. Filter Teacher Cards
        document.querySelectorAll('.modal-teacher-card').forEach(card => {
            const name = card.getAttribute('data-teacher-name') || '';
            const emp = card.getAttribute('data-teacher-emp') || '';
            const subjects = card.getAttribute('data-teacher-subjects') || '';
            const match = !query || name.includes(query) || emp.includes(query) || subjects.includes(query);
            card.style.display = match ? '' : 'none';
        });

        // 2. Filter Subject Cards
        document.querySelectorAll('.modal-subject-card').forEach(card => {
            const name = card.getAttribute('data-subject-name') || '';
            const code = card.getAttribute('data-subject-code') || '';
            const teachers = card.getAttribute('data-teachers') || '';
            const match = !query || name.includes(query) || code.includes(query) || teachers.includes(query);
            card.style.display = match ? '' : 'none';
        });
    }

    function filterModalSubjectsByClass(classId) {
        // Toggle pill styling
        document.querySelectorAll('.modal-class-pill').forEach(pill => {
            if (pill.getAttribute('data-class') === String(classId)) {
                pill.className = "modal-class-pill px-3 py-1 rounded-lg text-xs font-bold transition bg-navy text-white shadow-sm";
            } else {
                pill.className = "modal-class-pill px-3 py-1 rounded-lg text-xs font-semibold transition bg-white text-stone-700 border border-stone-200 hover:border-navy";
            }
        });

        document.querySelectorAll('.modal-subject-card').forEach(card => {
            if (classId === 'all' || card.getAttribute('data-class-id') === String(classId)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterMainTableClass(classId) {
        closeAssignAnalysisModal();
        const select = document.querySelector('select[name="class_id"]');
        if (select) {
            select.value = classId;
            document.getElementById('filterForm').submit();
        }
    }

    // Modal background click or ESC key to close
    document.getElementById('assignAnalysisModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeAssignAnalysisModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAssignAnalysisModal();
            closeImportModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@endsection
