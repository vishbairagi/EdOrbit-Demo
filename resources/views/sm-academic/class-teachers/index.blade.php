@extends('layouts.app')
@section('header', 'Class Teachers')
@section('content')

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-heading">Import Class Teachers</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ route('academic.class-teachers.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="mb-4 flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="text-sm text-gray-700">Need a format guide?</span>
                <a href="{{ route('academic.class-teachers.import-demo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center">
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

<div class="w-full">
    {{-- Top Header Section (Normal Text Without Color Grading) --}}
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <div>
            <h3 class="text-base font-bold text-heading">Class Teachers</h3>
            <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp; Total Class Teachers: {{ $classTeachers->total() }} &nbsp;·&nbsp; Total Groups: {{ $totalGroups }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('academic.class-teachers.export') }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i> Export CSV
            </a>
            <button onclick="openImportModal()" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i data-lucide="upload" class="w-3.5 h-3.5 mr-1"></i> Import CSV
            </button>
        </div>
    </div>

    {{-- ══ Sleek Compact KPI Stats Bar ══ --}}
    <div class="bg-white rounded-lg border border-gray-200/90 shadow-2xs px-3.5 py-2 mb-3.5 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-3 lg:gap-5">
            {{-- Stat 1: Staff Assigned --}}
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md flex items-center justify-center shrink-0" style="background:#f5ece0; color:#B47A42;">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Staff Assigned</span>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="font-semibold text-heading text-xs">{{ $assignedStaffCount }}/{{ $totalStaff }}</span>
                        <span class="text-[9px] font-medium text-amber-700 bg-amber-50 px-1 py-0.2 rounded border border-amber-200/60 leading-none">{{ $staffCoveragePercent }}%</span>
                    </div>
                </div>
            </div>

            <div class="h-6 w-px bg-gray-200/80 hidden sm:block"></div>

            {{-- Stat 2: Classes Covered --}}
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Classes Covered</span>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="font-semibold text-heading text-xs">{{ $assignedClassesCount }}/{{ $totalClassesCount }}</span>
                        <span class="text-[9px] font-medium text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200/60 leading-none">{{ $classCoveragePercent }}%</span>
                    </div>
                </div>
            </div>

            <div class="h-6 w-px bg-gray-200/80 hidden sm:block"></div>

            {{-- Stat 3: Active Assignments --}}
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Assignments</span>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="font-semibold text-heading text-xs">{{ $totalAssignmentsCount }}</span>
                        <span class="text-[10px] text-gray-400 font-normal">total</span>
                        <span class="text-[9px] text-gray-500 bg-gray-100 px-1 py-0.2 rounded leading-none">{{ $totalGroups }} Groups</span>
                    </div>
                </div>
            </div>

            <div class="h-6 w-px bg-gray-200/80 hidden lg:block"></div>

            {{-- Stat 4: Active Teachers --}}
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                </div>
                <div>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide block leading-none">Active Teachers</span>
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="font-semibold text-heading text-xs">{{ $teacherBreakdown->count() }}</span>
                        <span class="text-[10px] text-gray-400 font-normal">assigned</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Teacher breakdown popover --}}
        @if($teacherBreakdown->count() > 0)
            <div class="relative group ml-auto">
                <button type="button" class="text-[11px] text-gray-600 hover:text-heading flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-colors">
                    <i data-lucide="list-filter" class="w-3 h-3 text-gray-400"></i>
                    <span>Breakdown</span>
                    <i data-lucide="chevron-down" class="w-2.5 h-2.5 text-gray-400"></i>
                </button>
                <div class="hidden group-hover:block absolute right-0 top-full mt-1 w-52 bg-white rounded-lg shadow-lg border border-gray-200 p-2 z-30">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 px-1">Top Assigned Teachers</div>
                    <div class="max-h-40 overflow-y-auto space-y-1">
                        @foreach($teacherBreakdown->take(8) as $tb)
                            <div class="flex items-center justify-between text-[11px] px-1.5 py-0.5 rounded hover:bg-gray-50">
                                <span class="truncate text-heading font-medium max-w-[130px]">{{ $tb->staff->name ?? 'Staff' }}</span>
                                <span class="text-[10px] text-gray-600 bg-gray-100 px-1.5 rounded">{{ $tb->count }} {{ Str::plural('class', $tb->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ══ Main Redesigned Split Grid (Left Table + Right Assign Dialog) ══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- ── LEFT SIDE: Unified Table Card ── --}}
        <div class="lg:col-span-7 xl:col-span-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Search & Filter Toolbar --}}
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50">
                    <form method="GET" action="{{ route('academic.class-teachers.index') }}" class="flex flex-wrap items-center gap-2">
                        <div class="relative flex-1 min-w-[160px]">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search staff, class, section..."
                                   class="w-full rounded-lg border border-gray-300 text-xs pl-8 pr-7 py-2 focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                            @if(request('search'))
                                <a href="{{ route('academic.class-teachers.index', request()->except('search', 'page')) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>

                        <select name="class_id" id="filter_class_id" onchange="updateFilterSections(); this.form.submit()" class="rounded-lg border border-gray-300 text-xs px-2.5 py-2 focus:outline-none bg-white text-heading">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>

                        <select name="section_id" id="filter_section_id" onchange="this.form.submit()" class="rounded-lg border border-gray-300 text-xs px-2.5 py-2 focus:outline-none bg-white text-heading">
                            <option value="">All Sections</option>
                        </select>

                        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-gray-300 text-xs px-2.5 py-2 focus:outline-none bg-white text-heading">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                        </select>

                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-white text-xs font-semibold bg-navy hover:bg-primary-dark transition shadow-2xs">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            Filter
                        </button>

                        @if(request('class_id') || request('section_id') || request('search') || (request('per_page') && request('per_page') != 10))
                            <a href="{{ route('academic.class-teachers.index') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium hover:bg-gray-100 text-red-600 transition">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Bulk Delete Toolbar --}}
                <div id="bulkToolbar" class="hidden flex items-center gap-3 bg-red-50 border-b border-red-200 px-4 py-2.5">
                    <span id="selectedCount" class="text-xs font-semibold text-red-700">0 selected</span>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.class-teachers.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
                        @csrf
                        <div id="bulkIdsContainer"></div>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-2xs">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete Selected
                        </button>
                    </form>
                    <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-navy">
                                <th class="px-3 py-2.5 w-10 text-center">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"
                                           class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" title="Select All">
                                </th>
                                <th class="text-left text-white font-semibold text-xs px-4 py-2.5 uppercase tracking-wider">Staff Teacher</th>
                                <th class="text-left text-white font-semibold text-xs px-4 py-2.5 uppercase tracking-wider">Class</th>
                                <th class="text-left text-white font-semibold text-xs px-4 py-2.5 uppercase tracking-wider">Section</th>
                                <th class="text-right text-white font-semibold text-xs px-4 py-2.5 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($classTeachers as $ct)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="checkbox" value="{{ $ct->id }}" class="row-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600" onchange="updateBulkToolbar()">
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="font-bold text-xs text-heading">{{ $ct->staff->name }}</div>
                                        <div class="text-2xs text-gray-500">{{ $ct->staff->email ?? ($ct->staff->employee_id ?? 'N/A') }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs font-semibold text-heading">
                                        {{ $ct->schoolClass->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-xs font-medium text-heading">
                                        {{ $ct->section->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" onclick="openEditModal({{ $ct->id }}, {{ $ct->staff_id }}, {{ $ct->class_id }}, {{ $ct->section_id ?? 'null' }})" class="p-1 rounded text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors" title="Edit">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form method="POST" action="{{ route('academic.class-teachers.destroy', $ct) }}" class="inline" onsubmit="return confirm('Remove this class teacher assignment?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1 rounded text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors" title="Remove">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center py-10 text-xs text-gray-500">
                                        <i data-lucide="users" class="w-8 h-8 text-gray-300 mx-auto mb-1.5"></i>
                                        No class teachers assigned yet for this session.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Pagination Footer --}}
                @if($classTeachers->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-white">
                        {{ $classTeachers->links() }}
                    </div>
                @else
                    <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <span>Showing <strong class="text-gray-700 font-semibold">{{ $classTeachers->count() }}</strong> of <strong class="text-gray-700 font-semibold">{{ $classTeachers->total() }}</strong> assignments</span>
                        <span class="text-gray-400">Page 1 of 1</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT SIDE: Assign Class Teacher Card (Clean Rectangular Card) ── --}}
        <div class="lg:col-span-5 xl:col-span-4 sticky top-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h4 class="text-xs font-bold text-heading flex items-center gap-1.5">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5 text-navy"></i>
                        Assign Class Teacher
                    </h4>
                    <span class="text-[10px] font-normal px-1.5 py-0.2 rounded" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        Multi-Class &amp; Section
                    </span>
                </div>

                <form method="POST" action="{{ route('academic.class-teachers.store') }}" id="assignTeacherForm" onsubmit="return validateAssignForm()" class="p-4 space-y-3">
                    @csrf

                    {{-- Staff Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Select Staff / Teacher <span class="text-red-500">*</span></label>
                        <select name="staff_id" id="assign_staff_id" required class="w-full p-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                            <option value="">-- Choose Teacher --</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ $staff->employee_id ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Searchable Classes & Sections Picker --}}
                    <div id="classPickerContainer" class="relative">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-gray-700">
                                Select Classes &amp; Sections <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <span id="selectedCountBadge" class="text-[10px] font-normal px-1.5 py-0.2 rounded" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                                    0 selected
                                </span>
                                <button type="button" id="clearAllSelectedBtn" onclick="clearAllSelections()" class="text-2xs text-red-500 hover:underline hidden font-medium">
                                    Clear all
                                </button>
                            </div>
                        </div>

                        {{-- Selected Badges Box --}}
                        <div id="selectedChipsBox" class="min-h-[38px] max-h-24 overflow-y-auto p-2 rounded-lg border border-gray-200 bg-gray-50/60 flex flex-wrap gap-1.5 items-center mb-2">
                            <span id="selectedChipsEmptyNote" class="text-2xs text-gray-400 italic">
                                No classes/sections selected. Type or search below to add.
                            </span>
                        </div>

                        {{-- Search Input with Clear Button & Toggle --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </div>
                            <input type="text" id="classSearchInput" 
                                   placeholder="Search class or section (e.g. 10th, Nursery, A)..." 
                                   autocomplete="off"
                                   oninput="filterClassesAndSections(this.value)"
                                   onfocus="openClassPickerDropdown()"
                                   class="w-full pl-8 pr-16 py-2 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy bg-white text-heading">
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                <button type="button" id="clearSearchTextBtn" onclick="clearSearchInput()" class="text-gray-400 hover:text-gray-600 hidden p-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" id="togglePickerDropdownBtn" onclick="togglePickerDropdown()" class="text-gray-400 hover:text-navy p-1" title="Toggle List">
                                    <i data-lucide="chevron-down" id="pickerChevronIcon" class="w-3.5 h-3.5 transition-transform duration-200"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Searchable Results Dropdown (Clean and compact) --}}
                        <div id="classPickerDropdown" class="hidden mt-1.5 border border-gray-200 rounded-lg bg-white shadow-lg overflow-hidden transition-all z-20">
                            <div class="p-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between text-2xs text-gray-500">
                                <span id="classesResultCount">{{ $classes->count() }} classes available</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="selectAllFilteredSections()" class="text-navy font-semibold hover:underline">Select visible</button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" onclick="closeClassPickerDropdown()" class="text-gray-500 hover:underline">Done</button>
                                </div>
                            </div>

                            <div id="classItemsContainer" class="max-h-56 overflow-y-auto divide-y divide-gray-100 p-1">
                                @forelse($classes as $c)
                                    <div class="class-search-row p-2 rounded hover:bg-gray-50 transition-colors"
                                         data-class-id="{{ $c->id }}"
                                         data-class-name="{{ strtolower($c->name) }}"
                                         data-sections="{{ strtolower($c->sections->pluck('name')->implode(' ')) }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-heading truncate">{{ $c->name }}</span>
                                                    @if($c->classGroup)
                                                        <span class="text-3xs text-gray-400 px-1 py-0.2 rounded bg-gray-100">{{ $c->classGroup->name }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-3xs text-gray-400">
                                                    {{ $c->sections->count() > 0 ? $c->sections->count() . ' ' . Str::plural('section', $c->sections->count()) : 'No sections' }}
                                                </span>
                                            </div>

                                            {{-- Section Pills or Class Select --}}
                                            <div class="flex flex-wrap items-center justify-end gap-1 shrink-0">
                                                @if($c->sections->count() > 0)
                                                    <button type="button" 
                                                            onclick="toggleAllSectionsOfClass({{ $c->id }}, '{{ addslashes($c->name) }}')"
                                                            id="class_all_btn_{{ $c->id }}"
                                                            class="class-all-toggle-btn px-1.5 py-0.5 rounded text-3xs font-medium border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 transition-colors">
                                                        All
                                                    </button>
                                                    @foreach($c->sections as $sec)
                                                        <button type="button"
                                                                id="pill_{{ $c->id }}_{{ $sec->id }}"
                                                                onclick="toggleSectionSelection({{ $c->id }}, '{{ addslashes($c->name) }}', {{ $sec->id }}, '{{ addslashes($sec->name) }}')"
                                                                class="sec-pill-btn px-2 py-0.5 rounded text-2xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-all flex items-center gap-1">
                                                            <span>{{ $sec->name }}</span>
                                                        </button>
                                                    @endforeach
                                                @else
                                                    <button type="button"
                                                            id="pill_{{ $c->id }}_0"
                                                            onclick="toggleSectionSelection({{ $c->id }}, '{{ addslashes($c->name) }}', 0, '')"
                                                            class="sec-pill-btn px-2.5 py-0.5 rounded text-2xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-all">
                                                        Select Class
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic p-3 text-center">No classes found.</p>
                                @endforelse
                                <p id="noSearchMatchNotice" class="hidden text-xs text-gray-400 italic p-4 text-center">
                                    No matching classes or sections found.
                                </p>
                            </div>
                        </div>

                        {{-- Hidden Form Inputs Container --}}
                        <div id="hiddenClassSectionsContainer"></div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="w-full py-2.5 px-4 bg-navy hover:bg-primary-dark text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Save &amp; Assign to Table
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center" style="background:rgba(0,0,0,.5);">
    <div class="rounded-xl p-6 w-full max-w-md mx-4 bg-white shadow-xl">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
            <h3 class="font-bold text-base text-heading">Edit Class Teacher Assignment</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Select Staff</label>
                <select name="staff_id" id="edit_staff_id" required class="w-full p-2.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Select Class</label>
                <select name="class_id" id="edit_class_id" required class="w-full p-2.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-5">
                <label class="block text-xs font-semibold mb-1 text-gray-700">Select Section</label>
                <select name="section_id" id="edit_section_id" required class="w-full p-2.5 rounded-lg border border-gray-300 text-xs focus:outline-none focus:ring-1 focus:ring-navy bg-white">
                    <option value="">Select section...</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-navy hover:bg-primary-dark transition">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Hidden Section Data Helper for Dynamic JS --}}
<select id="availableSections" class="hidden">
    @foreach($classes as $c)
        @foreach($c->sections as $sec)
            <option value="{{ $sec->id }}" data-class-id="{{ $c->id }}">{{ $sec->name }}</option>
        @endforeach
    @endforeach
</select>

<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }
    document.getElementById('importModal')?.addEventListener('click', function(e) { if (e.target === this) closeImportModal(); });

    // Multi-Class & Section Searchable State Management
    const selectedClassSectionsMap = new Map();

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeJsString(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    function openClassPickerDropdown() {
        const dropdown = document.getElementById('classPickerDropdown');
        const chevron = document.getElementById('pickerChevronIcon');
        if (dropdown) dropdown.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeClassPickerDropdown() {
        const dropdown = document.getElementById('classPickerDropdown');
        const chevron = document.getElementById('pickerChevronIcon');
        if (dropdown) dropdown.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function togglePickerDropdown() {
        const dropdown = document.getElementById('classPickerDropdown');
        if (dropdown && dropdown.classList.contains('hidden')) {
            openClassPickerDropdown();
        } else {
            closeClassPickerDropdown();
        }
    }

    function clearSearchInput() {
        const input = document.getElementById('classSearchInput');
        if (input) {
            input.value = '';
            filterClassesAndSections('');
            input.focus();
        }
    }

    function filterClassesAndSections(query) {
        query = (query || '').toLowerCase().trim();
        openClassPickerDropdown();

        const clearBtn = document.getElementById('clearSearchTextBtn');
        if (clearBtn) {
            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        const rows = document.querySelectorAll('.class-search-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const className = row.dataset.className || '';
            const sections = row.dataset.sections || '';
            const matches = !query || className.includes(query) || sections.includes(query);

            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countLabel = document.getElementById('classesResultCount');
        if (countLabel) {
            countLabel.textContent = `${visibleCount} classes ${query ? 'found' : 'available'}`;
        }

        const notice = document.getElementById('noSearchMatchNotice');
        if (notice) {
            notice.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    function toggleSectionSelection(classId, className, sectionId, sectionName) {
        const key = `${classId}_${sectionId}`;
        if (selectedClassSectionsMap.has(key)) {
            selectedClassSectionsMap.delete(key);
        } else {
            selectedClassSectionsMap.set(key, { classId, className, sectionId, sectionName });
        }
        updateSelectedUI();
    }

    function toggleAllSectionsOfClass(classId, className) {
        const row = document.querySelector(`.class-search-row[data-class-id="${classId}"]`);
        if (!row) return;

        const pillBtns = row.querySelectorAll('.sec-pill-btn');
        if (pillBtns.length === 0) return;

        // Check if all are already selected
        let allSelected = true;
        pillBtns.forEach(btn => {
            const secId = parseInt(btn.id.split('_')[2]) || 0;
            if (!selectedClassSectionsMap.has(`${classId}_${secId}`)) {
                allSelected = false;
            }
        });

        pillBtns.forEach(btn => {
            const parts = btn.id.split('_');
            const secId = parseInt(parts[2]) || 0;
            const secName = btn.querySelector('span')?.textContent.trim() || '';
            const key = `${classId}_${secId}`;

            if (allSelected) {
                selectedClassSectionsMap.delete(key);
            } else {
                selectedClassSectionsMap.set(key, { classId, className, sectionId: secId, sectionName: secName });
            }
        });

        updateSelectedUI();
    }

    function selectAllFilteredSections() {
        const visibleRows = document.querySelectorAll('.class-search-row:not([style*="display: none"])');
        visibleRows.forEach(row => {
            const classId = parseInt(row.dataset.classId);
            const className = row.querySelector('.text-heading')?.textContent.trim() || '';
            const pillBtns = row.querySelectorAll('.sec-pill-btn');

            pillBtns.forEach(btn => {
                const parts = btn.id.split('_');
                const secId = parseInt(parts[2]) || 0;
                const secName = btn.querySelector('span')?.textContent.trim() || '';
                const key = `${classId}_${secId}`;
                selectedClassSectionsMap.set(key, { classId, className, sectionId: secId, sectionName: secName });
            });
        });

        updateSelectedUI();
    }

    function clearAllSelections() {
        selectedClassSectionsMap.clear();
        updateSelectedUI();
    }

    function updateSelectedUI() {
        // 1. Update Section Pill styling
        document.querySelectorAll('.sec-pill-btn').forEach(btn => {
            const parts = btn.id.split('_');
            const classId = parts[1];
            const secId = parts[2];
            const isSelected = selectedClassSectionsMap.has(`${classId}_${secId}`);

            if (isSelected) {
                btn.style.cssText = 'background:#B47A42; color:#ffffff; border-color:#B47A42;';
                btn.className = 'sec-pill-btn px-2.5 py-0.5 rounded text-2xs font-bold shadow-2xs transition-all flex items-center gap-1';
            } else {
                btn.style.cssText = '';
                btn.className = 'sec-pill-btn px-2.5 py-0.5 rounded text-2xs font-semibold border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 transition-all flex items-center gap-1';
            }
        });

        // 2. Update Class "All" toggle buttons
        document.querySelectorAll('.class-all-toggle-btn').forEach(btn => {
            const classId = parseInt(btn.id.replace('class_all_btn_', ''));
            const row = document.querySelector(`.class-search-row[data-class-id="${classId}"]`);
            if (!row) return;

            const pillBtns = row.querySelectorAll('.sec-pill-btn');
            let allSelected = pillBtns.length > 0;
            pillBtns.forEach(p => {
                const secId = parseInt(p.id.split('_')[2]) || 0;
                if (!selectedClassSectionsMap.has(`${classId}_${secId}`)) {
                    allSelected = false;
                }
            });

            if (allSelected) {
                btn.style.cssText = 'background:#f5ece0; color:#B47A42; border-color:#DED5CB; font-weight:700;';
            } else {
                btn.style.cssText = '';
                btn.className = 'class-all-toggle-btn px-1.5 py-0.5 rounded text-3xs font-medium border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 transition-colors';
            }
        });

        // 3. Render Selected Badges in chips box
        const chipsBox = document.getElementById('selectedChipsBox');
        const emptyNote = document.getElementById('selectedChipsEmptyNote');
        const clearBtn = document.getElementById('clearAllSelectedBtn');
        const badge = document.getElementById('selectedCountBadge');
        const count = selectedClassSectionsMap.size;

        if (badge) {
            badge.textContent = `${count} selected`;
        }

        if (count === 0) {
            if (emptyNote) emptyNote.classList.remove('hidden');
            if (clearBtn) clearBtn.classList.add('hidden');
            if (chipsBox) chipsBox.innerHTML = '<span id="selectedChipsEmptyNote" class="text-2xs text-gray-400 italic">No classes/sections selected. Type or search below to add.</span>';
        } else {
            if (clearBtn) clearBtn.classList.remove('hidden');
            let chipsHtml = '';
            selectedClassSectionsMap.forEach((item, key) => {
                const label = item.sectionName ? `${item.className} (${item.sectionName})` : item.className;
                chipsHtml += `
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-normal shadow-2xs" style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">
                        <span>${escapeHtml(label)}</span>
                        <button type="button" onclick="toggleSectionSelection(${item.classId}, '${escapeJsString(item.className)}', ${item.sectionId}, '${escapeJsString(item.sectionName)}')" class="text-gray-400 hover:text-red-500 font-medium ml-0.5 leading-none" title="Remove">
                            &times;
                        </button>
                    </span>
                `;
            });
            if (chipsBox) chipsBox.innerHTML = chipsHtml;
        }

        // 4. Update hidden form inputs
        const hiddenContainer = document.getElementById('hiddenClassSectionsContainer');
        if (hiddenContainer) {
            let hiddenHtml = '';
            selectedClassSectionsMap.forEach((item) => {
                const val = item.sectionId > 0 ? item.sectionId : '';
                hiddenHtml += `<input type="hidden" name="class_sections[${item.classId}][]" value="${val}">`;
            });
            hiddenContainer.innerHTML = hiddenHtml;
        }
    }

    function validateAssignForm() {
        if (selectedClassSectionsMap.size === 0) {
            alert('Please select at least one class or section.');
            const input = document.getElementById('classSearchInput');
            if (input) {
                input.focus();
                openClassPickerDropdown();
            }
            return false;
        }
        return true;
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const container = document.getElementById('classPickerContainer');
        if (container && !container.contains(e.target)) {
            closeClassPickerDropdown();
        }
    });

    // Filter Dropdown cascading
    const filterClassSelect = document.getElementById('filter_class_id');
    const filterSectionSelect = document.getElementById('filter_section_id');
    const availableSections = document.getElementById('availableSections');
    const selectedFilterSectionId = '{{ request('section_id') }}';

    function updateFilterSections() {
        if (!filterSectionSelect) return;
        filterSectionSelect.replaceChildren(new Option('All Sections', ''));
        if (!filterClassSelect || filterClassSelect.value === '') return;

        for (const sectionOption of availableSections.options) {
            if (sectionOption.dataset.classId === filterClassSelect.value) {
                let opt = new Option(sectionOption.textContent, sectionOption.value);
                if (sectionOption.value === selectedFilterSectionId) {
                    opt.selected = true;
                }
                filterSectionSelect.add(opt);
            }
        }
    }
    updateFilterSections();

    // Edit Modal cascading
    const editClassSelect = document.getElementById('edit_class_id');
    const editSectionSelect = document.getElementById('edit_section_id');

    function updateEditSections(selectedSectionId = '') {
        if (!editSectionSelect) return;
        editSectionSelect.replaceChildren(new Option('Select section...', ''));
        let hasSections = false;

        for (const sectionOption of availableSections.options) {
            if (sectionOption.dataset.classId === editClassSelect.value) {
                editSectionSelect.add(new Option(sectionOption.textContent, sectionOption.value));
                hasSections = true;
            }
        }

        editSectionSelect.disabled = editClassSelect.value === '';
        if (editClassSelect.value !== '' && !hasSections) {
            editSectionSelect.add(new Option('No sections available for this class', ''));
        }
        if (selectedSectionId) {
            editSectionSelect.value = selectedSectionId;
        }
    }

    editClassSelect?.addEventListener('change', () => updateEditSections(''));

    function openEditModal(id, staff_id, class_id, section_id) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editForm').action = "/academic/class-teachers/" + id;
        document.getElementById('edit_staff_id').value = staff_id;
        document.getElementById('edit_class_id').value = class_id;
        updateEditSections(section_id);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Bulk Delete Selection JS
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
                if (toolbar) toolbar.classList.remove('hidden');
                if (countEl) countEl.textContent = checked.length + ' selected';
            } else {
                if (toolbar) toolbar.classList.add('hidden');
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
            if (toolbar) toolbar.classList.add('hidden');
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
