@extends('layouts.app')
@section('header', 'Class Timetable')
@section('content')

    <style>
        .days-dropdown-scroll {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .days-dropdown-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .days-dropdown-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .days-dropdown-scroll::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }
        .days-dropdown-scroll::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>

    <div class="w-full">
        {{-- Top Header Section --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <div>
                <h3 class="text-base font-bold text-heading">Class Timetable</h3>
                <p class="text-xs text-gray-500 mt-0.5">Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp; Total Routines: {{ $totalRoutinesCount }} &nbsp;·&nbsp; Scheduled Classes: {{ $classesWithRoutinesCount }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('academic.class-timetable.index') }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Refresh
                </a>
                <button type="button" onclick="openCreateRoutineModal()" class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition-colors shadow-2xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> Add New Routine
                </button>
            </div>
        </div>

        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs"
                 style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-emerald-950">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs bg-red-50 text-red-700 border border-red-200">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-950">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 rounded-xl text-xs bg-red-50 text-red-700 border border-red-200 shadow-2xs">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Please check form errors:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ══ Full-Width Table Card ══ --}}
        <div class="w-full">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                {{-- Filter Toolbar --}}
                <div class="px-3.5 py-2.5 border-b border-gray-200 bg-gray-50/40">
                    <form method="GET" action="{{ route('academic.class-timetable.index') }}" class="flex flex-wrap items-center gap-2">
                        {{-- Class Filter --}}
                        <select name="class_id" id="filter_class_id" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>

                        {{-- Section Filter --}}
                        <select name="section_id" id="filter_section_id" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2.5 py-1.5 focus:outline-none bg-white text-heading shadow-2xs">
                            <option value="">All Sections</option>
                            @php
                                $selectedClass = $classes->firstWhere('id', request('class_id'));
                                $sections = $selectedClass ? $selectedClass->sections : $classes->flatMap->sections->unique('id');
                            @endphp
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ request('section_id') == $s->id ? 'selected' : '' }}>
                                    Section {{ $s->name }}{{ !request('class_id') && $s->schoolClass ? ' ('.$s->schoolClass->name.')' : '' }}
                                </option>
                            @endforeach
                            <option value="none" {{ request('section_id') === 'none' ? 'selected' : '' }}>No Section / General</option>
                        </select>

                        {{-- Filter Button --}}
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-white text-[11px] font-semibold bg-navy hover:bg-primary-dark transition shadow-2xs">
                            <i data-lucide="filter" class="w-3 h-3"></i> Filter
                        </button>

                        {{-- Reset Button --}}
                        @if(request('class_id') || request('section_id') || (request('per_page') && request('per_page') != 10))
                            <a href="{{ route('academic.class-timetable.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-gray-300 text-[11px] font-medium hover:bg-gray-100 text-red-600 transition shadow-2xs" title="Reset Filters">
                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Reset
                            </a>
                        @endif

                        {{-- Per-Page Dropdown (Pagination) --}}
                        <select name="per_page" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 text-[11px] px-2 py-1.5 focus:outline-none bg-white text-heading shadow-2xs cursor-pointer"
                                title="Rows per page">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 / page</option>
                        </select>

                        {{-- Bulk Delete Button (Hidden by default, shown only when row checkboxes selected) --}}
                        <button type="button" id="bulkDeleteBtn" onclick="confirmBulkDelete()" style="display: none !important;"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-[11px] font-bold bg-red-600 hover:bg-red-700 transition shadow-2xs cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Bulk Delete (<span id="selectedCountBadge">0</span>)
                        </button>

                        {{-- Right Add Routine Trigger in Toolbar --}}
                        <button type="button" onclick="openCreateRoutineModal()" class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-white text-[11px] font-bold bg-navy hover:bg-primary-dark transition shadow-2xs shrink-0 cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Routine
                        </button>
                    </form>
                </div>

                {{-- Table: Class, Section, Scheduled Routines, Actions (Small, Crisp Typography) --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-navy text-white text-[10px] uppercase tracking-wider font-semibold">
                                <th class="px-2.5 py-2 w-9 text-center">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllRows(this)"
                                           class="w-3.5 h-3.5 rounded border-gray-300 text-navy focus:ring-0 cursor-pointer" title="Select All">
                                </th>
                                <th class="px-3 py-2">Class</th>
                                <th class="px-3 py-2">Section</th>
                                <th class="px-3 py-2 text-center whitespace-nowrap">Subjects</th>
                                <th class="px-3 py-2 text-center whitespace-nowrap">Scheduled Routines</th>
                                <th class="px-3 py-2 whitespace-nowrap">Days Active</th>
                                <th class="px-3 py-2 text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-[11px]">
                            @forelse($rows as $idx => $row)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    {{-- Select Checkbox --}}
                                    <td class="px-2.5 py-2 text-center align-middle">
                                        <input type="checkbox" name="selected_keys[]" value="{{ $row['class_id'] }}:{{ $row['section_id'] ?? 'none' }}"
                                               data-name="{{ $row['class_name'] }} - {{ $row['section_name'] }}"
                                               data-routines="{{ $row['routines_count'] }}"
                                               onchange="handleRowCheckboxChange()"
                                               class="row-checkbox w-3.5 h-3.5 rounded border-gray-300 text-navy focus:ring-0 cursor-pointer">
                                    </td>

                                    {{-- Class --}}
                                    <td class="px-3 py-2 align-middle">
                                        <div class="font-semibold text-[11px] text-heading flex items-center gap-1.5">
                                            <i data-lucide="graduation-cap" class="w-3 h-3 text-navy"></i>
                                            <span>{{ $row['class_name'] }}</span>
                                        </div>
                                    </td>

                                    {{-- Section --}}
                                    <td class="px-3 py-2 align-middle whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background:#f5ece0; color:#211B17; border: 1px solid #DED5CB;">
                                            {{ $row['section_name'] }}
                                        </span>
                                    </td>

                                    {{-- Subjects --}}
                                    <td class="px-3 py-2 text-center align-middle whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">
                                            <i data-lucide="book" class="w-2.5 h-2.5 text-gray-500"></i>
                                            {{ $row['subjects_count'] }}
                                        </span>
                                    </td>

                                    {{-- Scheduled Routines --}}
                                    <td class="px-3 py-2 text-center align-middle whitespace-nowrap">
                                        @if($row['routines_count'] > 0)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <i data-lucide="check-circle" class="w-2.5 h-2.5 text-emerald-600"></i>
                                                {{ $row['routines_count'] }} {{ Str::plural('Routine', $row['routines_count']) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-[10px] font-normal text-gray-400 bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                                                Not Scheduled
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Days Active --}}
                                    <td class="px-3 py-2 align-middle whitespace-nowrap">
                                        @if(!empty($row['days']))
                                            <div class="flex items-center gap-0.5 flex-wrap max-w-xs">
                                                @foreach($row['days'] as $day)
                                                    <span class="text-[8px] font-semibold uppercase tracking-wider px-1 py-0.2 rounded bg-amber-50 text-amber-900 border border-amber-200">
                                                        {{ substr($day, 0, 3) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">-</span>
                                        @endif
                                    </td>

                                    {{-- Actions: View Time Table, Add Routine, Delete Time Table --}}
                                    <td class="px-3 py-2 text-right whitespace-nowrap align-middle">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- View Time Table Button --}}
                                            <a href="{{ route('academic.class-timetable.show', ['schoolClass' => $row['class_id'], 'section' => $row['section_id'] ?? 0]) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                               title="View Time Table">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-600"></i>
                                                <span>View</span>
                                            </a>

                                            {{-- Quick Add Routine Button --}}
                                            <button type="button"
                                                    onclick="quickAddRoutine({{ $row['class_id'] }}, '{{ $row['section_id'] ?? '' }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-black transition-colors shadow-2xs"
                                                    title="Add Routine for this class & section">
                                                <i data-lucide="plus" class="w-3.5 h-3.5 text-gray-600"></i>
                                                <span>Add Routine</span>
                                            </button>

                                            {{-- Delete Entire Class Timetable Option --}}
                                            @if($row['routines_count'] > 0)
                                                <form method="POST" action="{{ route('academic.class-timetable.destroy-timetable') }}" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete ALL {{ $row['routines_count'] }} timetable routines for {{ $row['class_name'] }} - {{ $row['section_name'] }}?');">
                                                    @csrf
                                                    <input type="hidden" name="school_class_id" value="{{ $row['class_id'] }}">
                                                    <input type="hidden" name="section_id" value="{{ $row['section_id'] ?? '' }}">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 bg-white border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 transition-colors shadow-2xs"
                                                            title="Delete entire timetable for this section">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-xs text-gray-500">
                                        <i data-lucide="calendar-x" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                                        <p class="font-bold text-gray-700 text-xs">No classes or routines found</p>
                                        <p class="text-[10px] text-gray-400 mt-1">Please create classes and sections first, then add routines.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Pagination Links --}}
                @if($rows->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 bg-white no-print">
                        {{ $rows->links() }}
                    </div>
                @endif

                {{-- Table Footer Summary --}}
                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50/50 flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-500">
                    <span>Showing <strong class="text-gray-700 font-semibold">{{ $rows->firstItem() ?? 0 }}</strong> to <strong class="text-gray-700 font-semibold">{{ $rows->lastItem() ?? 0 }}</strong> of <strong class="text-gray-700 font-semibold">{{ $rows->total() }}</strong> class section(s)</span>
                    <span>Total Routines Scheduled: <strong class="text-navy font-bold">{{ $totalRoutinesCount }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Form for Bulk Delete --}}
    <form id="bulkDeleteForm" method="POST" action="{{ route('academic.class-timetable.bulk-destroy') }}" class="hidden">
        @csrf
        <div id="bulkDeleteInputsContainer"></div>
    </form>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── ADD NEW ROUTINE MODAL (Matches Image 1 for Day Selection) ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="createRoutineModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-visible my-6 border border-gray-100 relative">
            {{-- Modal Header --}}
            <div class="px-5 py-3.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between rounded-t-2xl">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-heading">Add New Routine / Timetable</h3>
                        <p class="text-[10px] text-gray-400">Schedule class period &amp; assign subject teacher</p>
                    </div>
                </div>
                <button type="button" onclick="closeCreateRoutineModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('academic.class-timetable.store') }}" id="createRoutineForm" class="p-5 space-y-4">
                @csrf

                {{-- Class & Section --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Class:
                        </label>
                        <select name="school_class_id" id="modal_class_id" required onchange="handleModalClassChange(this.value)"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Section:
                        </label>
                        <select name="section_id" id="modal_section_id"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                </div>

                {{-- Subject & Assigned Teacher --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Subject:
                        </label>
                        <select name="subject_id" id="modal_subject_id" required onchange="handleModalSubjectChange(this.value)"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Subject</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Assigned Teacher:
                        </label>
                        <select name="staff_id" id="modal_staff_id"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Teacher (Choose subject first)</option>
                        </select>
                        <p id="teacher_note" class="text-[10px] text-gray-400 mt-1">Sirf us subject ke assigned teacher dikhenge.</p>
                    </div>
                </div>

                {{-- Day Selection (Custom Dropdown Matching Image 1) --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">
                        <span class="text-red-500">*</span> Day:
                    </label>

                    {{-- Day Multi-Select Container --}}
                    <div class="relative" id="daySelectWrapper">
                        {{-- Trigger Button / Display Box --}}
                        <div id="daySelectTrigger" onclick="toggleDayDropdown()"
                             class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading flex items-center justify-between cursor-pointer shadow-2xs hover:border-gray-400 select-none">
                            <span id="daySelectLabel" class="text-gray-500">Select Day</span>
                            <i data-lucide="chevron-down" id="daySelectIcon" class="w-3.5 h-3.5 text-gray-400 transition-transform"></i>
                        </div>

                        {{-- Dropdown Panel Matching Image 1 --}}
                        <div id="dayDropdownPanel" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl z-50 p-2.5 text-xs">
                            {{-- Search Filter Input inside Dropdown (Image 1 blue outline) --}}
                            <div class="mb-2">
                                <input type="text" id="daySearchInput" onkeyup="filterDaysList(this.value)" placeholder=""
                                       class="w-full border-2 border-blue-600 rounded px-2.5 py-1 text-xs focus:outline-none">
                            </div>

                            {{-- Select All / Deselect All Action Bar (Image 1) --}}
                            <div class="flex items-center justify-between px-2 py-1.5 bg-gray-100 rounded text-[11px] font-semibold text-gray-700 mb-1.5">
                                <button type="button" onclick="selectAllDays()" class="text-blue-600 hover:text-blue-800 cursor-pointer font-semibold">Select All</button>
                                <button type="button" onclick="deselectAllDays()" class="text-gray-600 hover:text-gray-800 cursor-pointer font-semibold">Deselect All</button>
                            </div>

                            {{-- Days List with Checkboxes (Image 1) - scrollable with styled scrollbar --}}
                            <div id="daysList" class="days-dropdown-scroll space-y-0.5" style="max-height: 135px; overflow-y: auto !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayName)
                                    <label class="day-option flex items-center gap-2.5 px-2.5 py-1.5 hover:bg-blue-50/60 rounded cursor-pointer select-none transition-colors">
                                        <input type="checkbox" name="days[]" value="{{ $dayName }}" onchange="updateDaySelection()"
                                               class="day-checkbox w-3.5 h-3.5 rounded border-gray-300 text-navy focus:ring-0 cursor-pointer">
                                        <span class="day-text text-xs text-gray-800 font-medium">{{ $dayName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Time Range & Room Number --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Start Time:
                        </label>
                        <input type="time" name="start_time" required
                               class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Ending Time:
                        </label>
                        <input type="time" name="end_time" required
                               class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Room Number:
                        </label>
                        <input type="text" name="room_number" placeholder="e.g. 101, Lab-1, Library"
                               class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeCreateRoutineModal()" class="px-4 py-2 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-2xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i> Save Routine
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for Dynamic Class, Subject & Teacher Filtering and Image 1 Day Dropdown --}}
    <script>
        // Store preloaded class data
        const classDataStore = @json($classDataMap);

        function openCreateRoutineModal() {
            document.getElementById('createRoutineModal')?.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }

        function closeCreateRoutineModal() {
            document.getElementById('createRoutineModal')?.classList.add('hidden');
            closeDayDropdown();
        }

        document.getElementById('createRoutineModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateRoutineModal();
        });

        // Quick add from table row
        function quickAddRoutine(classId, sectionId) {
            openCreateRoutineModal();
            const classSelect = document.getElementById('modal_class_id');
            if (classSelect) {
                classSelect.value = classId;
                handleModalClassChange(classId, sectionId);
            }
        }

        // Handle Class Change -> populate Sections and Subjects
        function handleModalClassChange(classId, preselectedSectionId = null) {
            const sectionSelect = document.getElementById('modal_section_id');
            const subjectSelect = document.getElementById('modal_subject_id');
            const staffSelect = document.getElementById('modal_staff_id');

            if (!sectionSelect || !subjectSelect || !staffSelect) return;

            sectionSelect.innerHTML = '<option value="">All Sections</option>';
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            staffSelect.innerHTML = '<option value="">Select Teacher (Choose subject first)</option>';

            if (!classId || !classDataStore[classId]) return;

            const cData = classDataStore[classId];

            // Populate Sections
            if (cData.sections && cData.sections.length > 0) {
                cData.sections.forEach(sec => {
                    const opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = `Section ${sec.name}`;
                    if (preselectedSectionId && sec.id == preselectedSectionId) {
                        opt.selected = true;
                    }
                    sectionSelect.appendChild(opt);
                });
            }

            // Populate Subjects
            if (cData.subjects && cData.subjects.length > 0) {
                cData.subjects.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name + (sub.code ? ` (${sub.code})` : '');
                    subjectSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No subjects assigned to this class';
                opt.disabled = true;
                subjectSelect.appendChild(opt);
            }
        }

        // Handle Subject Change -> Filter Teachers to ONLY assigned teachers for this subject!
        function handleModalSubjectChange(subjectId) {
            const classId = document.getElementById('modal_class_id')?.value;
            const staffSelect = document.getElementById('modal_staff_id');
            const note = document.getElementById('teacher_note');
            if (!staffSelect) return;

            staffSelect.innerHTML = '<option value="">Select Teacher</option>';

            if (!classId || !subjectId || !classDataStore[classId]) return;

            const cData = classDataStore[classId];
            const subject = cData.subjects.find(s => s.id == subjectId);

            if (subject && subject.teachers && subject.teachers.length > 0) {
                subject.teachers.forEach(teacher => {
                    const opt = document.createElement('option');
                    opt.value = teacher.id;
                    opt.textContent = teacher.name;
                    staffSelect.appendChild(opt);
                });

                // Auto-select if only 1 assigned teacher
                if (subject.teachers.length === 1) {
                    staffSelect.selectedIndex = 1;
                }
                if (note) {
                    note.textContent = `Found ${subject.teachers.length} assigned teacher(s) for ${subject.name}.`;
                    note.className = 'text-[10px] text-emerald-600 mt-1 font-semibold';
                }
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No teacher assigned to this subject';
                opt.disabled = true;
                staffSelect.appendChild(opt);
                if (note) {
                    note.textContent = 'Is subject ke liye abhi koi teacher assign nahi hai (Assign Subjects me assign karein).';
                    note.className = 'text-[10px] text-amber-600 mt-1';
                }
            }
        }

        // ══ Image 1 Day Selection Dropdown Logic ══
        function toggleDayDropdown() {
            const panel = document.getElementById('dayDropdownPanel');
            const icon = document.getElementById('daySelectIcon');
            if (!panel) return;
            const isHidden = panel.classList.contains('hidden');
            if (isHidden) {
                panel.classList.remove('hidden');
                icon?.classList.add('rotate-180');
                document.getElementById('daySearchInput')?.focus();
            } else {
                closeDayDropdown();
            }
        }

        function closeDayDropdown() {
            const panel = document.getElementById('dayDropdownPanel');
            const icon = document.getElementById('daySelectIcon');
            panel?.classList.add('hidden');
            icon?.classList.remove('rotate-180');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('daySelectWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeDayDropdown();
            }
        });

        function filterDaysList(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('#daysList .day-option').forEach(el => {
                const text = el.querySelector('.day-text')?.textContent.toLowerCase() || '';
                if (text.includes(q)) {
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        function selectAllDays() {
            document.querySelectorAll('.day-checkbox').forEach(cb => { cb.checked = true; });
            updateDaySelection();
        }

        function deselectAllDays() {
            document.querySelectorAll('.day-checkbox').forEach(cb => { cb.checked = false; });
            updateDaySelection();
        }

        function updateDaySelection() {
            const checked = Array.from(document.querySelectorAll('.day-checkbox:checked')).map(cb => cb.value);
            const label = document.getElementById('daySelectLabel');
            if (!label) return;

            if (checked.length === 0) {
                label.textContent = 'Select Day';
                label.className = 'text-gray-500';
            } else if (checked.length === 7) {
                label.textContent = 'All Days Selected (7 Days)';
                label.className = 'text-heading font-semibold';
            } else {
                label.textContent = checked.join(', ');
                label.className = 'text-heading font-semibold truncate max-w-[280px]';
            }
        }

        // ══ Bulk Delete Logic ══
        function toggleSelectAllRows(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = masterCheckbox.checked;
                cb.closest('tr')?.classList.toggle('bg-red-50/60', cb.checked);
            });
            updateBulkDeleteState();
        }

        function handleRowCheckboxChange() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const master = document.getElementById('selectAllCheckbox');
            if (master) {
                master.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
                master.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
            }
            checkboxes.forEach(cb => {
                cb.closest('tr')?.classList.toggle('bg-red-50/60', cb.checked);
            });
            updateBulkDeleteState();
        }

        function updateBulkDeleteState() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const btn = document.getElementById('bulkDeleteBtn');
            const badge = document.getElementById('selectedCountBadge');
            if (badge) badge.textContent = checked.length;

            if (btn) {
                if (checked.length > 0) {
                    btn.style.setProperty('display', 'inline-flex', 'important');
                } else {
                    btn.style.setProperty('display', 'none', 'important');
                }
            }
        }

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one class section to delete.');
                return;
            }

            let totalRoutines = 0;
            checked.forEach(cb => {
                totalRoutines += parseInt(cb.dataset.routines || '0', 10);
            });

            const msg = `Are you sure you want to delete timetables for the ${checked.length} selected class section(s)?` + 
                        (totalRoutines > 0 ? `\n\nThis will remove approximately ${totalRoutines} routine slot(s).` : '');

            if (confirm(msg)) {
                const form = document.getElementById('bulkDeleteForm');
                const container = document.getElementById('bulkDeleteInputsContainer');
                if (!form || !container) return;

                container.innerHTML = '';
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_keys[]';
                    input.value = cb.value;
                    container.appendChild(input);
                });

                form.submit();
            }
        }

        // Ensure smooth mouse wheel scrolling inside the days list and initialize bulk delete state
        document.addEventListener('DOMContentLoaded', function() {
            updateBulkDeleteState();
            const daysList = document.getElementById('daysList');
            if (daysList) {
                daysList.addEventListener('wheel', function(e) {
                    e.stopPropagation();
                }, { passive: true });
            }
        });
    </script>

@endsection
