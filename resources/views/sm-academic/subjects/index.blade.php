@extends('layouts.app')

@section('header', 'Subjects')

@section('content')

{{-- Page Header --}}
<div class="bg-navy rounded-xl shadow-md px-5 py-3 flex items-center justify-between mb-6">
    <div class="flex items-center text-white font-bold text-base">
        <i data-lucide="book-open" class="w-4 h-4 mr-2"></i>
        Subjects
        <span class="ml-2 text-white/70 text-xs font-normal">Session: {{ $session->name }}</span>
    </div>
    <div class="flex items-center gap-2">
        @unless($isClassTeacher)
        <a href="{{ route('academic.subject-types.index') }}"
           class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="tag" class="w-3.5 h-3.5 mr-1"></i>
            Manage Types
        </a>
        {{-- Export --}}
        <a href="{{ route('academic.subjects.export') }}"
           class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i>
            Export CSV
        </a>
        {{-- Import --}}
        <button onclick="document.getElementById('subjectImportModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 bg-white/20 text-white text-xs font-semibold rounded-lg hover:bg-white/30 transition-colors">
            <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i>
            Import CSV
        </button>
        @endunless
        <button onclick="document.getElementById('addSubjectModal').classList.remove('hidden')"
                class="inline-flex items-center px-3 py-1.5 bg-white text-navy text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
            Add Subject
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2"
     style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
    <i data-lucide="check-circle" class="w-4 h-4"></i>
    {{ session('success') }}
</div>
@endif
@if(session('import_errors') && count(session('import_errors')))
<div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background:#fef0f0; color:#7a181c; border:1px solid #f5c0c3;">
    <p class="font-semibold mb-1">Some rows were skipped:</p>
    <ul class="list-disc ml-4 space-y-0.5">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Search & Class Filter Bar --}}
<div class="bg-card rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('academic.subjects.index') }}" id="filterForm" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
            {{-- Class Dropdown --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Filter by Class:</label>
                <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="w-full md:w-64">
                <label class="block text-xs font-medium text-body mb-1">Search Subject / Code:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject name or code..."
                           class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 pr-8 focus:outline-none focus:ring-1 focus:ring-navy">
                    @if(request('search'))
                        <a href="{{ route('academic.subjects.index', array_filter(['class_id' => request('class_id')])) }}"
                           class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-end mt-auto gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition bg-navy">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Filter
                </button>
                @if(request('class_id') || request('search'))
                    <a href="{{ route('academic.subjects.index') }}"
                       class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-200 text-xs font-medium text-body hover:bg-gray-50 transition">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </div>
        </div>

        {{-- View Mode Switcher (All vs Grouped by Class) --}}
        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg self-end md:self-center">
            <button type="button" onclick="switchView('table')" id="btnViewTable"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all shadow-sm bg-white text-navy">
                <i data-lucide="list" class="w-3.5 h-3.5 inline-block mr-1"></i> Single Table
            </button>
            <button type="button" onclick="switchView('grouped')" id="btnViewGrouped"
                    class="px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 hover:text-navy transition-all">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 inline-block mr-1"></i> Grouped by Class
            </button>
        </div>
    </form>
</div>

{{-- Empty State --}}
@if($subjects->isEmpty())
    <div class="bg-card rounded-xl border border-gray-100 shadow-sm p-10 text-center">
        <i data-lucide="book-open" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
        <p class="text-lg font-medium text-heading">No subjects found.</p>
        <p class="text-sm text-body mt-1">Try adjusting your filters or add subjects for this class.</p>
        <button onclick="document.getElementById('addSubjectModal').classList.remove('hidden')"
                class="mt-4 inline-flex items-center px-4 py-2 bg-navy text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i> Add Subject
        </button>
    </div>
@else

    {{-- VIEW 1: SINGLE TABLE VIEW --}}
    <div id="viewTableContainer" class="bg-card rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between" style="background:#F5F1EB;">
            <span class="text-xs font-semibold text-body">Total Subjects: <strong>{{ $subjects->count() }}</strong></span>
            @if(request('class_id'))
                <span class="text-xs font-semibold text-navy">Filtered for Class ID: {{ request('class_id') }}</span>
            @endif
        </div>
        {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.subjects.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy">
                    <th class="px-4 py-2 w-10 text-center">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                    </th>
                    <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Subject Name</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Class</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Code</th>
                        <th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Type</th>
                        @unless($isClassTeacher)<th class="px-4 py-3 text-left text-white font-semibold text-xs uppercase tracking-wider">Actions</th>@endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subjects as $index => $subject)
                        <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $subject->id }}">
                        <td class="px-4 py-2 text-center w-10">
                            <input type="checkbox" value="{{ $subject->id }}" onchange="updateBulkToolbar()"
                                class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600">
                        </td>
                            <td class="px-4 py-3 text-body">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-heading">{{ $subject->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i data-lucide="graduation-cap" class="w-3 h-3 mr-1"></i>
                                    {{ $subject->schoolClass?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($subject->code)
                                    <code class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono text-heading border">{{ $subject->code }}</code>
                                @else
                                    <span class="text-body text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($subject->subjectType)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                        {{ $subject->subjectType->name }}
                                    </span>
                                @else
                                    <span class="text-body text-xs">—</span>
                                @endif
                            </td>
                            @unless($isClassTeacher)
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditSubject({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ addslashes($subject->code ?? '') }}', {{ $subject->school_class_id }}, {{ $subject->subject_type_id ?? 'null' }})"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white rounded-md hover:opacity-90 bg-navy">
                                        <i data-lucide="pencil" class="w-3 h-3 mr-1"></i> Edit
                                    </button>
                                    <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                                          onsubmit="return confirm('Delete subject {{ addslashes($subject->name) }}?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white rounded-md hover:opacity-90"
                                                style="background:#c0392b;">
                                            <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Delete
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

    {{-- VIEW 2: GROUPED BY CLASS VIEW --}}
    <div id="viewGroupedContainer" class="hidden space-y-6 mb-6">
        @foreach($subjectsByClass as $className => $classSubjects)
            <div class="bg-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Class Header Banner --}}
                <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between" style="background:#F5F1EB;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-navy flex items-center justify-center text-white text-xs font-bold">
                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="font-bold text-base text-heading">{{ $className }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                            {{ $classSubjects->count() }} subject(s)
                        </span>
                    </div>
                    @php
                        $targetClassObj = $classes->firstWhere('name', $className);
                    @endphp
                    @if($targetClassObj)
                        <button onclick="openAddSubjectForClass({{ $targetClassObj->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white rounded-md hover:opacity-90 transition bg-navy">
                            <i data-lucide="plus" class="w-3 h-3"></i> Add to {{ $className }}
                        </button>
                    @endif
                </div>

                {{-- Class Subjects Table --}}
                {{-- ══ Bulk Delete Toolbar (auto-injected) ══ --}}
<div id="bulkToolbar" class="hidden mb-3 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 animate-pulse-once">
    <span id="selectedCount" class="text-sm font-semibold text-red-700">0 selected</span>
    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.subjects.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
        @csrf
        <div id="bulkIdsContainer"></div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
        </button>
    </form>
    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
</div>
<div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2.5 text-left w-12">#</th>
                                <th class="px-4 py-2.5 text-left">Subject Name</th>
                                <th class="px-4 py-2.5 text-left">Code</th>
                                <th class="px-4 py-2.5 text-left">Type</th>
                                @unless($isClassTeacher)<th class="px-4 py-2.5 text-left">Actions</th>@endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($classSubjects as $idx => $subject)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3 text-body text-xs font-medium">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-heading">{{ $subject->name }}</td>
                                    <td class="px-4 py-3">
                                        @if($subject->code)
                                            <code class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono text-heading border">{{ $subject->code }}</code>
                                        @else
                                            <span class="text-body text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($subject->subjectType)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                                {{ $subject->subjectType->name }}
                                            </span>
                                        @else
                                            <span class="text-body text-xs">—</span>
                                        @endif
                                    </td>
                                    @unless($isClassTeacher)
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <button onclick="openEditSubject({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ addslashes($subject->code ?? '') }}', {{ $subject->school_class_id }}, {{ $subject->subject_type_id ?? 'null' }})"
                                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded hover:opacity-90 bg-navy">
                                                <i data-lucide="pencil" class="w-3 h-3 mr-1"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                                                  onsubmit="return confirm('Delete subject {{ addslashes($subject->name) }}?')" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-white rounded hover:opacity-90"
                                                        style="background:#c0392b;">
                                                    <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> Delete
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

{{-- Add Subject Modal --}}
<div id="addSubjectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
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
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Add Subject
                </button>
                <button type="button" onclick="document.getElementById('addSubjectModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Subject Modal --}}
<div id="editSubjectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45);">
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
            </div>
            <div class="flex gap-3 mt-5">
                <button type="submit" class="flex-1 bg-navy text-white text-sm font-medium py-2 rounded-lg hover:opacity-90">
                    Update Subject
                </button>
                <button type="button" onclick="document.getElementById('editSubjectModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 text-heading text-sm font-medium py-2 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="subjectImportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,0.45); backdrop-filter:blur(2px);">
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
    });

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

    function openEditSubject(id, name, code, classId, subjectTypeId) {
        document.getElementById('editSubjectForm').action = '/academic/subjects/' + id;
        document.getElementById('editSubjectName').value = name;
        document.getElementById('editSubjectCode').value = code;
        document.getElementById('editSubjectClass').value = classId;
        const typeSelect = document.getElementById('editSubjectTypeId');
        typeSelect.value = subjectTypeId !== null ? subjectTypeId : '';
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
    document.getElementById('subjectImportModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>


    {{-- ══ Bulk Select JS (auto-injected) ══ --}}
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
