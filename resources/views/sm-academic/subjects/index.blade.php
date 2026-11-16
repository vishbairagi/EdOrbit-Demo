@extends('layouts.app')

@section('header', 'Subjects')

@section('content')

{{-- Page Header --}}
{{-- Top Header Section (Matching /academic/class-teachers, class-sections, mediums) --}}
<div class="flex items-center justify-between mb-3 flex-wrap gap-2">
    <div>
        <h3 class="text-base font-bold text-heading">Subjects Management</h3>
        <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name }} &nbsp;·&nbsp; Total Subjects: {{ $subjects->total() }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button type="button" onclick="openSubjectAnalysisModal('classes')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="pie-chart" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Session Analysis
        </button>

        @unless($isClassTeacher)
        <a href="{{ route('academic.subject-types.index') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="tag" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Manage Types
        </a>
        <a href="{{ route('academic.subjects.export') }}"
           class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Export CSV
        </a>
        <button type="button" onclick="document.getElementById('subjectImportModal').classList.remove('hidden')"
                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Import CSV
        </button>
        @endunless
        <button type="button" onclick="document.getElementById('addSubjectModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add Subject
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
@if(session('import_errors') && count(session('import_errors')))
<div class="mb-3.5 px-4 py-2.5 rounded-lg text-xs" style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <p class="font-semibold mb-1">Some rows were skipped:</p>
    <ul class="list-disc ml-4 space-y-0.5">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ══ Sleek Compact KPI Stats Bar ══ --}}
<div class="bg-white rounded-lg border border-gray-200/90 shadow-2xs px-3.5 py-2 mb-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
    <div class="flex flex-wrap items-center gap-3 lg:gap-5">
        {{-- Stat 1: Curriculum Load --}}
        <div class="flex items-center gap-2 cursor-pointer group" onclick="openSubjectAnalysisModal('classes')" title="Click for class details">
            <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 bg-blue-50 text-blue-600">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Curriculum Load</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="font-semibold text-heading text-xs">{{ number_format($analytics['totalSubjects']) }}</span>
                    <span class="text-[9px] font-medium text-blue-700 bg-blue-50 px-1 py-0.2 rounded border border-blue-200/60 leading-none">Avg {{ $analytics['avgSubjectsPerClass'] }} / Class</span>
                </div>
            </div>
        </div>

        <div class="h-6 w-px bg-gray-200/80 hidden sm:block"></div>

        {{-- Stat 2: Class Coverage --}}
        <div class="flex items-center gap-2 cursor-pointer group" onclick="openSubjectAnalysisModal('classes')" title="Click to analyze classes">
            <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Class Coverage</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="font-semibold text-heading text-xs">{{ $analytics['classesWithSubjects'] }}/{{ $analytics['totalClasses'] }}</span>
                    <span class="text-[9px] font-medium text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200/60 leading-none">{{ $analytics['classCoveragePercent'] }}%</span>
                </div>
            </div>
        </div>

        <div class="h-6 w-px bg-gray-200/80 hidden sm:block"></div>

        {{-- Stat 3: Subject Types --}}
        <div class="flex items-center gap-2 cursor-pointer group" onclick="openSubjectAnalysisModal('types')" title="Click for type breakdown">
            <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Subject Types</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="font-semibold text-heading text-xs">{{ $analytics['distinctTypesCount'] }} Types</span>
                    <span class="text-[9px] font-medium text-purple-700 bg-purple-50 px-1 py-0.2 rounded border border-purple-200/60 leading-none">{{ $analytics['typedPercent'] }}%</span>
                </div>
            </div>
        </div>

        <div class="h-6 w-px bg-gray-200/80 hidden lg:block"></div>

        {{-- Stat 4: Theory & Practical --}}
        <div class="flex items-center gap-2 cursor-pointer group" onclick="openSubjectAnalysisModal('format')" title="Click for format breakdown">
            <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" style="background:#f5ece0; color:#B47A42;">
                <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Theory / Lab</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="font-semibold text-heading text-xs">{{ $analytics['theoryCount'] }} / {{ $analytics['practicalCount'] }}</span>
                    <span class="text-[9px] font-medium text-amber-800 bg-amber-50 px-1 py-0.2 rounded border border-amber-200/60 leading-none">{{ $analytics['theoryPercent'] }}% Theory</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick button to open full analysis modal --}}
    <button type="button" onclick="openSubjectAnalysisModal('classes')"
            class="text-[11px] text-gray-600 hover:text-heading flex items-center gap-1 px-2.5 py-1 rounded-md bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-colors ml-auto">
        <i data-lucide="pie-chart" class="w-3 h-3 text-gray-400"></i>
        <span>Session Analysis</span>
        <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
    </button>
</div>

{{-- Search, Class Filter & Per-Page Bar --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3.5 mb-3.5">
    <form method="GET" action="{{ route('academic.subjects.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            {{-- Class Dropdown --}}
            <div class="w-full md:w-56">
                <label class="block text-xs font-semibold text-heading mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white cursor-pointer">
                    <option value="">All Classes ({{ $classes->count() }})</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="w-full md:w-60">
                <label class="block text-xs font-semibold text-heading mb-1">Search Subject / Code:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject name or code..."
                           class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-1.5 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.subjects.index', array_filter(['class_id' => request('class_id'), 'per_page' => request('per_page')])) }}"
                           class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Per-Page Dropdown --}}
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
                @if(request('class_id') || request('search') || (request('per_page') && request('per_page') != 10))
                    <a href="{{ route('academic.subjects.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-medium text-red-600 hover:bg-gray-100 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- View Mode Switcher (All vs Grouped by Class) --}}
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg self-end md:self-center">
            <button type="button" onclick="switchView('table')" id="btnViewTable"
                    class="px-2.5 py-1 rounded-md text-xs font-semibold transition-all shadow-2xs bg-white text-navy">
                <i data-lucide="list" class="w-3.5 h-3.5 inline-block mr-1"></i> Single Table
            </button>
            <button type="button" onclick="switchView('grouped')" id="btnViewGrouped"
                    class="px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 hover:text-navy transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 inline-block mr-1"></i> Grouped by Class
            </button>
        </div>
    </form>
</div>

{{-- Empty State --}}
@if($subjects->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center">
        <i data-lucide="book-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-base font-bold text-heading">No subjects found.</p>
        <p class="text-xs text-gray-500 mt-1">Try adjusting your search filters or add subjects for this session.</p>
        <button onclick="document.getElementById('addSubjectModal').classList.remove('hidden')"
                class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-xs font-semibold rounded-lg hover:bg-primary-dark transition shadow-2xs">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add Subject
        </button>
    </div>
@else

    {{-- VIEW 1: SINGLE TABLE VIEW --}}
    <div id="viewTableContainer" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-4 py-2.5 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-gray-50/50">
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-gray-600">
                    Showing: <strong class="text-gray-900 font-bold">{{ $subjects->firstItem() ?? 0 }} - {{ $subjects->lastItem() ?? 0 }}</strong> of <strong class="text-gray-900 font-bold">{{ $subjects->total() }}</strong> subjects
                </span>
                <span class="text-xs text-gray-300">|</span>
                <span class="text-xs text-gray-600 font-medium">Session: <strong class="text-gray-900">{{ $session->name }}</strong></span>
            </div>
            @if(request('class_id'))
                @php $activeFilteredClass = $classes->firstWhere('id', request('class_id')); @endphp
                <span class="text-xs font-medium text-gray-800 bg-white px-2.5 py-0.5 rounded-md border border-gray-300">
                    Filtered Class: {{ $activeFilteredClass?->name ?? 'ID '.request('class_id') }}
                </span>
            @endif
        </div>

        {{-- ══ Bulk Delete Toolbar ══ --}}
        <div id="bulkToolbar" class="hidden flex items-center gap-3 bg-red-50 border-b border-red-200 px-4 py-2.5">
            <span id="selectedCount" class="text-xs font-semibold text-red-700">0 selected</span>
            <form id="bulkDeleteForm" method="POST" action="{{ route('academic.subjects.bulk-destroy') }}" onsubmit="return confirmBulkDelete(event)">
                @csrf
                <div id="bulkIdsContainer"></div>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                </button>
            </form>
            <button type="button" onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead>
                    <tr class="bg-navy">
                        <th class="px-3 py-2.5 w-10 text-center">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                        </th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Numbers</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Name</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Code</th>
                        <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Type & Format</th>
                        @unless($isClassTeacher)<th class="px-4 py-2.5 text-right text-white font-bold text-xs uppercase tracking-wider">Actions</th>@endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($subjects as $index => $subject)
                        <tr class="hover:bg-gray-50/80 transition-colors" data-id="{{ $subject->id }}">
                            <td class="px-3 py-2.5 text-center w-10">
                                <input type="checkbox" value="{{ $subject->id }}" onchange="updateBulkToolbar()"
                                    class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                            </td>
                            <td class="px-4 py-2.5 text-gray-500 font-medium text-xs">{{ $subjects->firstItem() + $index }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-gray-950">{{ $subject->name }}</span>
                                    @if($subject->is_active === false)
                                        <span class="text-[10px] font-semibold text-gray-400">(Inactive)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="font-bold text-xs text-gray-900">
                                    {{ $subject->schoolClass?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-mono font-medium text-gray-800">{{ $subject->code ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1.5 flex-wrap text-xs">
                                    <span class="font-medium text-gray-800">{{ $subject->subjectType?->name ?? 'Untyped' }}</span>
                                    <span class="text-gray-300">·</span>
                                    <span class="text-gray-600">{{ ($subject->type === 'practical' || strtolower($subject->subjectType?->name ?? '') === 'practical') ? 'Practical / Lab' : 'Theory' }}</span>
                                </div>
                            </td>
                            @unless($isClassTeacher)
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openEditSubject({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ addslashes($subject->code ?? '') }}', {{ $subject->school_class_id }}, {{ $subject->subject_type_id ?? 'null' }}, '{{ $subject->type ?? 'theory' }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                            title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-600"></i>
                                        <span>Edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                                          onsubmit="return confirm('Delete subject {{ addslashes($subject->name) }}?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-white border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 transition-colors shadow-2xs"
                                                title="Delete">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endunless
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Links Bar --}}
        @if($subjects->hasPages())
            <div class="px-4 py-2.5 border-t border-gray-200 bg-white">
                {{ $subjects->links() }}
            </div>
        @else
            <div class="px-4 py-2.5 border-t border-gray-200 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Showing <strong class="text-gray-700 font-semibold">{{ $subjects->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $subjects->total() }}</strong> subjects</span>
                <span class="text-gray-400">Page 1 of 1</span>
            </div>
        @endif
    </div>

    {{-- VIEW 2: GROUPED BY CLASS VIEW --}}
    <div id="viewGroupedContainer" class="hidden space-y-4 mb-6">
        @foreach($subjectsByClass as $className => $classSubjects)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Class Header Banner --}}
                <div class="px-4 py-2.5 border-b border-gray-200 flex items-center justify-between bg-gray-50/60">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-heading">{{ $className }}</h3>
                        <span class="text-xs text-gray-500 font-medium">
                            ({{ $classSubjects->count() }} {{ Str::plural('subject', $classSubjects->count()) }})
                        </span>
                    </div>
                    @php
                        $targetClassObj = $classes->firstWhere('name', $className);
                    @endphp
                    @if($targetClassObj)
                        <button type="button" onclick="openAddSubjectForClass({{ $targetClassObj->id }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs">
                            <i data-lucide="plus" class="w-3.5 h-3.5 text-gray-600"></i>
                            <span>Add to {{ $className }}</span>
                        </button>
                    @endif
                </div>

                {{-- Class Subjects Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead>
                            <tr class="bg-navy">
                                <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider w-16">Numbers</th>
                                <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Name</th>
                                <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Code</th>
                                <th class="px-4 py-2.5 text-left text-white font-bold text-xs uppercase tracking-wider">Subject Type & Format</th>
                                @unless($isClassTeacher)<th class="px-4 py-2.5 text-right text-white font-bold text-xs uppercase tracking-wider">Actions</th>@endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($classSubjects as $idx => $subject)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-2.5 text-gray-500 font-medium text-xs">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2.5 font-bold text-xs text-gray-950">{{ $subject->name }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-xs font-mono font-medium text-gray-800">{{ $subject->code ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-1.5 flex-wrap text-xs">
                                            <span class="font-medium text-gray-800">{{ $subject->subjectType?->name ?? 'Untyped' }}</span>
                                            <span class="text-gray-300">·</span>
                                            <span class="text-gray-600">{{ ($subject->type === 'practical' || strtolower($subject->subjectType?->name ?? '') === 'practical') ? 'Practical / Lab' : 'Theory' }}</span>
                                        </div>
                                    </td>
                                    @unless($isClassTeacher)
                                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="openEditSubject({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ addslashes($subject->code ?? '') }}', {{ $subject->school_class_id }}, {{ $subject->subject_type_id ?? 'null' }}, '{{ $subject->type ?? 'theory' }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                                    title="Edit">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5 text-gray-600"></i>
                                                <span>Edit</span>
                                            </button>
                                            <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                                                  onsubmit="return confirm('Delete subject {{ addslashes($subject->name) }}?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-white border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 transition-colors shadow-2xs"
                                                        title="Delete">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endunless
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

@endif

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- ── MODAL BOX: PARTICULAR SESSION SUBJECT ANALYSIS (SHOWMODEL BOX) ── --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="subjectAnalysisModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-6"
     style="background:rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-150">

        {{-- Modal Top Banner Header --}}
        <div class="bg-navy text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-amber-300">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold leading-tight">Session Subject Analysis</h3>
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
                    <span>{{ $analytics['totalClasses'] }} Classes</span>
                    <span class="text-white/40">|</span>
                    <span>{{ $analytics['distinctTypesCount'] }} Types</span>
                </div>
                <button type="button" onclick="closeSubjectAnalysisModal()"
                        class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Modal Interactive Tabs & Quick Search Bar --}}
        <div class="bg-stone-50 border-b border-stone-200 px-6 py-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button type="button" id="analysisBtn_classes" onclick="switchAnalysisTab('classes')"
                        class="analysis-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 bg-navy text-white shadow-sm">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>Subjects of Particular Class</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ count($analytics['classBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_types" onclick="switchAnalysisTab('types')"
                        class="analysis-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                    <span>Subject Types Breakdown</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-stone-200 text-stone-700">{{ count($analytics['typeBreakdown']) }}</span>
                </button>
                <button type="button" id="analysisBtn_format" onclick="switchAnalysisTab('format')"
                        class="analysis-tab-btn px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 text-stone-600 hover:text-navy hover:bg-stone-200/60">
                    <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
                    <span>Theory vs Practical</span>
                </button>
            </div>

            {{-- Real-time Quick Filter Search --}}
            <div class="relative w-full md:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" id="modalSearchInput" oninput="filterModalSearch(this.value)"
                       placeholder="Filter classes, subjects, types..."
                       class="w-full rounded-lg border border-stone-200 text-xs pl-8 pr-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
            </div>
        </div>

        {{-- Modal Content Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">

            {{-- ── TAB 1: SUBJECTS OF PARTICULAR CLASS ── --}}
            <div id="analysisTab_classes" class="analysis-tab-pane space-y-4">

                {{-- Class Quick Select Bar --}}
                <div class="p-3.5 rounded-xl border border-stone-200 bg-stone-50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-stone-700">Filter Particular Class:</span>
                        <select id="modalClassSelectFilter" onchange="filterModalByClass(this.value)"
                                class="rounded-lg border border-stone-300 text-xs px-3 py-1.5 bg-white font-medium focus:outline-none focus:ring-1 focus:ring-navy">
                            <option value="all">All Classes ({{ count($analytics['classBreakdown']) }})</option>
                            @foreach($analytics['classBreakdown'] as $cItem)
                                <option value="{{ $cItem['id'] }}">{{ $cItem['name'] }} ({{ $cItem['count'] }} subjects)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-xs text-stone-500">
                        Total Subjects in this Session: <strong class="text-navy">{{ $analytics['totalSubjects'] }}</strong>
                    </div>
                </div>

                {{-- Classes Cards Grid --}}
                <div id="modalClassCardsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['classBreakdown'] as $cItem)
                        <div class="modal-class-card border border-stone-200 rounded-xl p-4 bg-white hover:border-navy/40 hover:shadow-sm transition-all"
                             data-class-id="{{ $cItem['id'] }}"
                             data-class-name="{{ strtolower($cItem['name']) }}">

                            {{-- Class Card Top Row --}}
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-navy/10 text-navy flex items-center justify-center font-bold text-xs">
                                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $cItem['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">Class Grade</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $cItem['count'] > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-stone-100 text-stone-500' }}">
                                        {{ $cItem['count'] }} {{ Str::plural('Subject', $cItem['count']) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Format Mini Badges --}}
                            @if($cItem['count'] > 0)
                            <div class="flex items-center gap-2 my-2.5 text-[11px]">
                                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 font-semibold">
                                    {{ $cItem['theory_count'] }} Theory
                                </span>
                                <span class="px-2 py-0.5 rounded bg-cyan-50 text-cyan-800 font-semibold">
                                    {{ $cItem['practical_count'] }} Practical
                                </span>
                                @if(count($cItem['types']) > 0)
                                <span class="text-stone-400">&bull; Types: {{ implode(', ', $cItem['types']) }}</span>
                                @endif
                            </div>

                            {{-- Subject Tags List for this Class --}}
                            <div class="space-y-1.5 my-3 max-h-48 overflow-y-auto pr-1">
                                @foreach($cItem['subjects'] as $sub)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-stone-50 border border-stone-100 text-xs">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="book" class="w-3.5 h-3.5 text-stone-400 flex-shrink-0"></i>
                                            <span class="font-bold text-heading truncate">{{ $sub->name }}</span>
                                            @if($sub->code)
                                                <code class="px-1.5 py-0.2 rounded bg-white text-[10px] font-mono border text-stone-600">{{ $sub->code }}</code>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                            @if($sub->subjectType)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700">
                                                    {{ $sub->subjectType->name }}
                                                </span>
                                            @endif
                                            @if($sub->type === 'practical' || strtolower($sub->subjectType?->name ?? '') === 'practical')
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800">Lab</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @else
                            <div class="my-4 py-4 text-center bg-stone-50 rounded-lg border border-dashed border-stone-200">
                                <p class="text-xs text-stone-500 font-medium">No subjects assigned to this class yet.</p>
                                <button type="button" onclick="openAddSubjectForClass({{ $cItem['id'] }}); closeSubjectAnalysisModal();"
                                        class="mt-2 text-xs text-navy font-bold hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="plus" class="w-3 h-3"></i> Add Subject Now
                                </button>
                            </div>
                            @endif

                            {{-- Card Footer --}}
                            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-xs">
                                <button type="button" onclick="filterMainTableClass({{ $cItem['id'] }})"
                                        class="text-navy font-semibold hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="filter" class="w-3 h-3"></i> Filter main table for this class
                                </button>
                                <span class="text-[10px] text-stone-400">Session: {{ $session->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No classes registered in this session.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 2: SUBJECT TYPES BREAKDOWN ── --}}
            <div id="analysisTab_types" class="analysis-tab-pane hidden space-y-4">
                <div class="p-3.5 rounded-xl border border-stone-200 bg-stone-50 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-stone-800">Subject Types Curriculum Weight</h4>
                        <p class="text-[11px] text-stone-500">Distribution of subjects across formal educational subject types in Session: {{ $session->name }}</p>
                    </div>
                    <div class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-200">
                        {{ $analytics['distinctTypesCount'] }} Distinct Types
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($analytics['typeBreakdown'] as $tItem)
                        <div class="modal-type-card border border-stone-200 rounded-xl p-4 bg-white hover:border-purple-300 hover:shadow-sm transition-all">
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs">
                                        <i data-lucide="tag" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-heading">{{ $tItem['name'] }}</h4>
                                        <span class="text-[11px] text-stone-400">{{ $tItem['description'] ?? 'Curriculum Category' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-purple-700">{{ $tItem['count'] }}</span>
                                    <span class="text-xs text-stone-500 font-medium block">({{ $tItem['percentage'] }}%)</span>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full bg-stone-100 rounded-full h-2 my-3 overflow-hidden">
                                <div class="bg-purple-600 h-2 rounded-full transition-all duration-700" style="width: {{ $tItem['percentage'] }}%;"></div>
                            </div>

                            {{-- Classes using this type --}}
                            <div class="text-xs text-stone-600 mb-3">
                                <span class="font-bold text-stone-700">Taught in classes:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @forelse($tItem['classes'] as $clsName)
                                        <span class="px-2 py-0.5 rounded bg-stone-100 text-stone-700 text-[11px] font-medium border border-stone-200">
                                            {{ $clsName }}
                                        </span>
                                    @empty
                                        <span class="text-stone-400 text-xs italic">Not assigned to any class yet</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Subjects preview --}}
                            <div class="pt-2.5 border-t border-stone-100">
                                <span class="text-[11px] font-bold text-stone-500 uppercase tracking-wider block mb-1">Subjects ({{ $tItem['count'] }}):</span>
                                <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                    @foreach($tItem['subjects'] as $s)
                                        <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-800 text-[11px] font-semibold border border-purple-100">
                                            {{ $s->name }} ({{ $s->schoolClass?->name ?? '—' }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-stone-400">No subject types found for this session.</div>
                    @endforelse
                </div>
            </div>

            {{-- ── TAB 3: THEORY VS PRACTICAL SPLIT ── --}}
            <div id="analysisTab_format" class="analysis-tab-pane hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Theory Subjects Box --}}
                    <div class="border border-stone-200 rounded-2xl p-5 bg-white shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shadow-sm"
                                     style="background: #B47A42;">
                                    <i data-lucide="book-marked" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-base text-heading">Theory Subjects</h4>
                                    <span class="text-xs text-stone-400">Classroom lectures & fundamental studies</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black" style="color: #B47A42;">{{ $analytics['theoryCount'] }}</span>
                                <span class="text-xs text-stone-500 font-bold block">{{ $analytics['theoryPercent'] }}% of curriculum</span>
                            </div>
                        </div>

                        {{-- Visual percentage gauge bar --}}
                        <div class="w-full bg-stone-100 rounded-full h-2.5 my-4 overflow-hidden">
                            <div class="h-2.5 rounded-full transition-all duration-700"
                                 style="background: #B47A42; width: {{ $analytics['theoryPercent'] }}%;"></div>
                        </div>

                        <div class="text-xs font-semibold text-stone-700 mb-2">Subject Directory:</div>
                        <div class="space-y-1.5 max-h-80 overflow-y-auto pr-1">
                            @forelse($analytics['classBreakdown'] as $c)
                                @php
                                    $thSubjects = $c['subjects']->filter(fn($s) => $s->type !== 'practical' && strtolower($s->subjectType?->name ?? '') !== 'practical');
                                @endphp
                                @if($thSubjects->count() > 0)
                                    <div class="modal-format-item p-2.5 rounded-lg bg-stone-50 border border-stone-100">
                                        <div class="text-xs font-bold text-heading flex items-center justify-between mb-1">
                                            <span>{{ $c['name'] }}</span>
                                            <span class="text-[10px] text-stone-400">{{ $thSubjects->count() }} Theory</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($thSubjects as $ts)
                                                <span class="px-2 py-0.5 rounded bg-white text-[11px] font-medium text-stone-700 border border-stone-200">
                                                    {{ $ts->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-stone-400 text-xs py-4 text-center">No theory subjects recorded.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Practical / Lab Subjects Box --}}
                    <div class="border border-stone-200 rounded-2xl p-5 bg-white shadow-sm">
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white bg-cyan-600 shadow-sm">
                                    <i data-lucide="flask-conical" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-base text-heading">Practical & Lab Subjects</h4>
                                    <span class="text-xs text-stone-400">Experimental, lab & hands-on coursework</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black text-cyan-700">{{ $analytics['practicalCount'] }}</span>
                                <span class="text-xs text-stone-500 font-bold block">{{ $analytics['practicalPercent'] }}% of curriculum</span>
                            </div>
                        </div>

                        {{-- Visual percentage gauge bar --}}
                        <div class="w-full bg-stone-100 rounded-full h-2.5 my-4 overflow-hidden">
                            <div class="bg-cyan-600 h-2.5 rounded-full transition-all duration-700"
                                 style="width: {{ $analytics['practicalPercent'] }}%;"></div>
                        </div>

                        <div class="text-xs font-semibold text-stone-700 mb-2">Subject Directory:</div>
                        <div class="space-y-1.5 max-h-80 overflow-y-auto pr-1">
                            @forelse($analytics['classBreakdown'] as $c)
                                @php
                                    $prSubjects = $c['subjects']->filter(fn($s) => $s->type === 'practical' || strtolower($s->subjectType?->name ?? '') === 'practical');
                                @endphp
                                @if($prSubjects->count() > 0)
                                    <div class="modal-format-item p-2.5 rounded-lg bg-cyan-50/50 border border-cyan-100">
                                        <div class="text-xs font-bold text-cyan-950 flex items-center justify-between mb-1">
                                            <span>{{ $c['name'] }}</span>
                                            <span class="text-[10px] text-cyan-700 font-bold">{{ $prSubjects->count() }} Lab/Practical</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($prSubjects as $ps)
                                                <span class="px-2 py-0.5 rounded bg-white text-[11px] font-medium text-cyan-900 border border-cyan-200">
                                                    {{ $ps->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="text-stone-400 text-xs py-4 text-center">No practical subjects recorded.</div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div class="bg-stone-50 border-t border-stone-200 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div class="text-xs text-stone-500">
                Analysis generated for academic session <strong>{{ $session->name }}</strong>
            </div>
            <button type="button" onclick="closeSubjectAnalysisModal()"
                    class="px-4 py-2 bg-stone-200 hover:bg-stone-300 text-stone-700 text-xs font-bold rounded-lg transition-colors">
                Close Analysis
            </button>
        </div>

    </div>
</div>

{{-- Add Subject Modal --}}
<div id="addSubjectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5 text-navy"></i>
                Add New Subject
            </h3>
            <button onclick="document.getElementById('addSubjectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('academic.subjects.store') }}">
            @csrf
            <div class="space-y-4">
                {{-- Subject Name (auto-generates code) --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Subject Name <span class="text-red-500">*</span></label>
                    <input type="text" id="addSubjectName" name="name" required placeholder="e.g. Mathematics"
                           oninput="autoGenerateCode(this.value, 'addSubjectCode')"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                {{-- Subject Code (auto-generated, but editable) --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">
                        Subject Code
                        <span class="ml-1 text-xs text-body font-normal">(auto-generated, editable)</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="addSubjectCode" name="code" placeholder="e.g. MATH"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-9 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <button type="button" onclick="regenerateCode('addSubjectName','addSubjectCode')"
                                title="Re-generate code"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy transition-colors">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                {{-- Assign to Class --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Assign to Class <span class="text-red-500">*</span></label>
                    <select id="addSubjectClassSelect" name="school_class_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="">— Select Class —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Type --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Subject Type</label>
                    @if($subjectTypes->isEmpty())
                        <div class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            No subject types added yet.
                            @unless($isClassTeacher)
                            <a href="{{ route('academic.subject-types.index') }}" class="underline font-medium">Add types here</a>.
                            @endunless
                        </div>
                    @else
                        <select name="subject_type_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                            <option value="">— Select Type (optional) —</option>
                            @foreach($subjectTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Subject Format: Theory vs Practical --}}
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Format / Nature</label>
                    <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="theory">Theory (Lecture)</option>
                        <option value="practical">Practical / Laboratory</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition">
                    Add Subject
                </button>
                <button type="button" onclick="document.getElementById('addSubjectModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Subject Modal --}}
<div id="editSubjectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45);">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                <i data-lucide="pencil" class="w-5 h-5 text-navy"></i>
                Edit Subject
            </h3>
            <button onclick="document.getElementById('editSubjectModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editSubjectForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Subject Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editSubjectName" name="name" required
                           oninput="autoGenerateCode(this.value, 'editSubjectCode')"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">
                        Subject Code
                        <span class="ml-1 text-xs text-body font-normal">(editable)</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="editSubjectCode" name="code"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-9 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        <button type="button" onclick="regenerateCode('editSubjectName','editSubjectCode')"
                                title="Re-generate code"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy transition-colors">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Assign to Class <span class="text-red-500">*</span></label>
                    <select id="editSubjectClass" name="school_class_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Subject Type</label>
                    <select id="editSubjectTypeId" name="subject_type_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="">— Select Type (optional) —</option>
                        @foreach($subjectTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-heading mb-1">Format / Nature</label>
                    <select id="editSubjectTypeFormat" name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy bg-white">
                        <option value="theory">Theory (Lecture)</option>
                        <option value="practical">Practical / Laboratory</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90 transition">
                    Update Subject
                </button>
                <button type="button" onclick="document.getElementById('editSubjectModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="subjectImportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
    <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background:#fff;">
        <div class="flex items-center justify-between px-6 py-4 bg-navy">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Import Subjects from CSV
            </div>
            <button onclick="document.getElementById('subjectImportModal').classList.add('hidden')"
                    class="text-white opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            {{-- Sample download --}}
            <div class="mb-5 p-4 rounded-xl flex items-center justify-between gap-3"
                 style="background:#fdf6ed; border:1px dashed #D4A055;">
                <div>
                    <p class="text-sm font-semibold" style="color:#7a4f1a;">Download Sample File</p>
                    <p class="text-xs mt-0.5" style="color:#a07040;">Use this template to prepare your CSV</p>
                </div>
                <a href="{{ route('academic.subjects.import-demo') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white whitespace-nowrap bg-navy">
                    <i data-lucide="download" class="w-3 h-3"></i>
                    Sample CSV
                </a>
            </div>

            {{-- Column legend --}}
            <div class="mb-5 p-3 rounded-lg text-xs" style="background:#f9f9f9; color:#555; border:1px solid #eee;">
                <p class="font-semibold mb-1.5 text-gray-800">CSV Columns (in order):</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-0.5">
                    <span>1. Subject Name *</span><span>2. Class Name *</span>
                    <span>3. Subject Code</span><span>4. Subject Type</span>
                </div>
                <p class="mt-2 text-amber-700">* Required. Class & Subject Type must match existing names exactly.</p>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('academic.subjects.import') }}" enctype="multipart/form-data">
                @csrf
                <label class="block text-sm font-medium mb-2 text-gray-800">Select CSV File</label>
                <div id="subjectDropZone"
                     class="relative rounded-xl border-2 border-dashed flex flex-col items-center justify-center py-8 px-4 cursor-pointer transition-all"
                     style="border-color:#3b4f6b; background:#f8fafc;"
                     onclick="document.getElementById('subjectCsvInput').click()"
                     ondragover="event.preventDefault(); this.style.background='#eef2f7';"
                     ondragleave="this.style.background='#f8fafc';"
                     ondrop="handleSubjectDrop(event)">
                    <i data-lucide="upload-cloud" class="w-8 h-8 mb-2" style="color:#3b4f6b;"></i>
                    <p class="text-sm font-medium" style="color:#3b4f6b;">Click or drag &amp; drop your CSV here</p>
                    <p id="subjectFileName" class="text-xs mt-1 text-gray-400">No file chosen</p>
                    <input type="file" id="subjectCsvInput" name="csv_file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('subjectFileName').textContent = this.files[0]?.name || 'No file chosen'">
                </div>
                <div class="flex gap-3 mt-5">
                    <button type="button"
                            onclick="document.getElementById('subjectImportModal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold border"
                            style="border-color:#ddd; color:#555; background:#fff;">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-white bg-navy">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ── Single Table vs Grouped Switcher ──
    function switchView(mode) {
        const table = document.getElementById('viewTableContainer');
        const grouped = document.getElementById('viewGroupedContainer');
        const btnTable = document.getElementById('btnViewTable');
        const btnGrouped = document.getElementById('btnViewGrouped');

        if (mode === 'grouped') {
            table?.classList.add('hidden');
            grouped?.classList.remove('hidden');
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('subjects_view_mode', 'grouped');
        } else {
            table?.classList.remove('hidden');
            grouped?.classList.add('hidden');
            btnTable.className = "px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy";
            btnGrouped.className = "px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all";
            localStorage.setItem('subjects_view_mode', 'table');
        }
    }

    // Restore view preference
    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('subjects_view_mode');
        if (pref === 'grouped') {
            switchView('grouped');
        }
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });

    // ── Analysis Modal Controller ──
    function openSubjectAnalysisModal(tab = 'classes') {
        const modal = document.getElementById('subjectAnalysisModal');
        if (modal) {
            modal.classList.remove('hidden');
            switchAnalysisTab(tab);
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function closeSubjectAnalysisModal() {
        const modal = document.getElementById('subjectAnalysisModal');
        if (modal) modal.classList.add('hidden');
    }

    function switchAnalysisTab(tabName) {
        document.querySelectorAll('.analysis-tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.analysis-tab-btn').forEach(btn => {
            btn.classList.remove('bg-navy', 'text-white', 'shadow-sm');
            btn.classList.add('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
        });

        const targetPane = document.getElementById('analysisTab_' + tabName);
        const targetBtn = document.getElementById('analysisBtn_' + tabName);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('text-stone-600', 'hover:text-navy', 'hover:bg-stone-200/60');
            targetBtn.classList.add('bg-navy', 'text-white', 'shadow-sm');
        }
        if (window.lucide) window.lucide.createIcons();
    }

    function filterModalByClass(classId) {
        const cards = document.querySelectorAll('.modal-class-card');
        cards.forEach(card => {
            if (classId === 'all' || card.getAttribute('data-class-id') === String(classId)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterModalSearch(query) {
        query = (query || '').toLowerCase().trim();

        // 1. Class cards filter
        document.querySelectorAll('.modal-class-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = (!query || text.includes(query)) ? '' : 'none';
        });

        // 2. Type cards filter
        document.querySelectorAll('.modal-type-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = (!query || text.includes(query)) ? '' : 'none';
        });

        // 3. Format items filter
        document.querySelectorAll('.modal-format-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }

    function filterMainTableClass(classId) {
        closeSubjectAnalysisModal();
        const select = document.querySelector('select[name="class_id"]');
        if (select) {
            select.value = classId;
            document.getElementById('filterForm').submit();
        }
    }

    // Modal background click or ESC key to close
    document.getElementById('subjectAnalysisModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSubjectAnalysisModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSubjectAnalysisModal();
    });

    // ── Add/Edit Subjects Helpers ──
    function openAddSubjectForClass(classId) {
        const select = document.getElementById('addSubjectClassSelect');
        if (select) select.value = classId;
        document.getElementById('addSubjectModal').classList.remove('hidden');
    }

    function autoGenerateCode(name, targetId) {
        const codeField = document.getElementById(targetId);
        if (!name.trim()) { codeField.value = ''; return; }

        const words = name.trim().toUpperCase().split(/[\s\-\/]+/).filter(Boolean);
        let code = '';
        if (words.length === 1) {
            code = words[0].replace(/[AEIOU]/g, '').substring(0, 4) || words[0].substring(0, 4);
        } else {
            code = words.slice(0, 5).map(w => w[0]).join('');
        }
        codeField.value = code;
    }

    function regenerateCode(nameId, codeId) {
        const name = document.getElementById(nameId).value;
        autoGenerateCode(name, codeId);
    }

    function openEditSubject(id, name, code, classId, subjectTypeId, type) {
        document.getElementById('editSubjectForm').action = '/academic/subjects/' + id;
        document.getElementById('editSubjectName').value = name;
        document.getElementById('editSubjectCode').value = code;
        document.getElementById('editSubjectClass').value = classId;
        const typeSelect = document.getElementById('editSubjectTypeId');
        if (typeSelect) typeSelect.value = (subjectTypeId !== null && subjectTypeId !== undefined) ? subjectTypeId : '';
        const formatSelect = document.getElementById('editSubjectTypeFormat');
        if (formatSelect) formatSelect.value = type ? type : 'theory';
        document.getElementById('editSubjectModal').classList.remove('hidden');
    }

    function handleSubjectDrop(e) {
        e.preventDefault();
        document.getElementById('subjectDropZone').style.background = '#f8fafc';
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('subjectCsvInput');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('subjectFileName').textContent = file.name;
        }
    }
    document.getElementById('subjectImportModal')?.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>

{{-- ══ Bulk Select JS ══ --}}
<script>
(function() {
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
        if (!confirm(checked.length + ' item(s) permanently delete honge. Sure hain?')) return false;
        var c = document.getElementById('bulkIdsContainer');
        c.innerHTML = '';
        checked.forEach(function(cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
            c.appendChild(inp);
        });
        return true;
    }
    window.toggleSelectAll   = toggleSelectAll;
    window.updateBulkToolbar = updateBulkToolbar;
    window.clearSelection    = clearSelection;
    window.confirmBulkDelete = confirmBulkDelete;
})();
</script>
@endsection
