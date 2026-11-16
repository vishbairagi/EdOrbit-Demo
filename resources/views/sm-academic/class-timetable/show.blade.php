@extends('layouts.app')
@section('header', 'Class Timetable')
@section('content')

    <style>
        /* ══ Custom Button & Top Bar Explicit Styling ══ */
        #blueHeaderBar {
            background-color: #0084ff !important;
            color: #ffffff !important;
        }
        #printClassTimetableBtn {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }
        #printClassTimetableBtn:hover {
            background-color: #059669 !important;
        }

        /* ══ Print Styles (Matching the attached PDF exactly) ══ */
        @media print {
            *, *::before, *::after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            @page {
                margin: 8mm;
                size: landscape;
            }

            html, body {
                background: #ffffff !important;
                color: #111827 !important;
                height: auto !important;
                overflow: visible !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                font-size: 10px !important;
            }

            aside#sidebar,
            #resizer,
            header,
            .no-print,
            .slot-actions,
            #blueHeaderBar,
            #printBtnContainer,
            #addRoutineModal,
            #editRoutineModal {
                display: none !important;
            }

            main, main > div.flex-1 {
                overflow: visible !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
                background: #ffffff !important;
            }

            .timetable-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                width: 100% !important;
            }

            .timetable-table {
                width: 100% !important;
                border-collapse: collapse !important;
                border: 1px solid #9ca3af !important;
                margin-top: 6px !important;
                table-layout: auto !important;
                page-break-inside: auto !important;
            }

            .timetable-table tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }

            .timetable-table th,
            .timetable-table td {
                border: 1px solid #9ca3af !important;
                padding: 6px 8px !important;
                vertical-align: top !important;
                font-size: 9.5px !important;
                line-height: 1.3 !important;
            }

            .timetable-day-col {
                width: 85px !important;
                min-width: 85px !important;
                max-width: 95px !important;
                font-weight: 700 !important;
                color: #111827 !important;
                background: #ffffff !important;
                border-right: 1px solid #9ca3af !important;
            }

            .slot-cell {
                border: 1px solid #9ca3af !important;
                background: #ffffff !important;
                padding: 6px 8px !important;
                vertical-align: top !important;
            }

            .print-school-header {
                display: block !important;
                text-align: center !important;
                margin-bottom: 8px !important;
                padding-bottom: 6px !important;
                border-bottom: 1px solid #9ca3af !important;
            }
        }

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

    <div class="w-full space-y-4">

        {{-- ══ Blue Header Bar (Matching Image 2) ══ --}}
        <div id="blueHeaderBar" style="background-color: #0084ff !important; color: #ffffff !important;" class="w-full text-white px-5 py-3 rounded-xl shadow-sm flex items-center justify-between flex-wrap gap-2 no-print">
            <div class="font-bold text-sm tracking-wide flex items-center gap-2" style="color: #ffffff !important;">
                <i data-lucide="calendar" class="w-4 h-4 text-white"></i>
                <span style="color: #ffffff !important;">Class Timetable: {{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}</span>
            </div>
            <div class="flex items-center gap-2">
                {{-- Add New Routine Button --}}
                <button type="button" onclick="openAddRoutineModal()"
                        style="background-color: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.5) !important;"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold transition-colors shadow-2xs hover:bg-white/30 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-white"></i>
                    Add New Routine
                </button>

                {{-- View All Button --}}
                <a href="{{ route('academic.class-timetable.index') }}"
                   style="background-color: rgba(255, 255, 255, 0.22) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.5) !important;"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold transition-colors shadow-2xs hover:bg-white/30">
                    <i data-lucide="calendar-range" class="w-3.5 h-3.5 text-white"></i>
                    View All
                </a>
            </div>
        </div>

        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs no-print"
                 style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-emerald-950">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs bg-red-50 text-red-700 border border-red-200 no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-950">&times;</button>
            </div>
        @endif

        {{-- ══ Center Title & Green Print Button (Matching Image 2) ══ --}}
        <div id="printBtnContainer" class="text-center py-2 no-print space-y-2.5">
            <h2 class="text-base font-bold text-heading">
                Class Timetable: {{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}
            </h2>
            <div>
                <button type="button" onclick="window.print()" id="printClassTimetableBtn"
                        style="background-color: #10b981 !important; color: #ffffff !important;"
                        class="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white rounded-md transition-all shadow-sm cursor-pointer hover:opacity-90">
                    <i data-lucide="printer" class="w-4 h-4 text-white"></i>
                    Print Class Timetable
                </button>
            </div>
        </div>

        {{-- ══ Main Timetable Container Card (Matching Image 2 & PDF Format) ══ --}}
        <div class="timetable-card bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">

            {{-- School Header (Matching Image 2 & 3 / PDF) --}}
            <div class="print-school-header text-center pb-4 mb-4 border-b border-gray-200">
                <h1 class="text-xl sm:text-2xl font-bold text-heading tracking-tight">
                    {{ $school->name ?? 'EdOrbit Academy' }}
                </h1>
                <div class="text-xs text-gray-500 mt-1 flex items-center justify-center gap-2 flex-wrap">
                    @if($school->phone)
                        <span>Phone: <strong class="text-gray-700 font-medium">{{ $school->phone }}</strong></span>
                        <span class="text-gray-300">|</span>
                    @endif
                    @if($school->email)
                        <span>Email: <strong class="text-gray-700 font-medium">{{ $school->email }}</strong></span>
                    @endif
                </div>
                @if($school->address)
                    <div class="text-xs text-gray-500 mt-0.5">
                        Address: {{ $school->address }}
                    </div>
                @endif
            </div>

            {{-- Sub-header Title above table (Image 2 & 3 / PDF) --}}
            <div class="mb-3">
                <h3 class="text-sm font-bold text-heading">
                    Class Timetable: {{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}
                </h3>
            </div>

            @php
                $maxSlots = 0;
                foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $d) {
                    $cnt = isset($timetableGrid[$d]) ? count($timetableGrid[$d]) : 0;
                    if ($cnt > $maxSlots) $maxSlots = $cnt;
                }
                $maxSlots = max(1, $maxSlots);
            @endphp

            {{-- ══ 7-Day Timetable Grid Table (Matching PDF Table Structure) ══ --}}
            <div class="overflow-x-auto">
                <table class="timetable-table w-full border-collapse border border-gray-200 text-xs">
                    <tbody>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayName)
                            @php
                                $daySlots = $timetableGrid[$dayName] ?? collect();
                            @endphp
                            <tr class="border-b border-gray-200">
                                {{-- Day Column --}}
                                <td class="timetable-day-col w-28 p-3 font-bold text-gray-800 bg-gray-50/50 border border-gray-200 align-top">
                                    {{ $dayName }}
                                </td>

                                @if($daySlots->isNotEmpty())
                                    @foreach($daySlots as $slot)
                                        <td class="slot-cell border border-gray-200 p-2.5 align-top">
                                            <div class="flex items-start justify-between gap-1.5">
                                                <div class="min-w-0 flex-1">
                                                    {{-- Subject & Code --}}
                                                    <div class="font-bold text-xs text-heading leading-tight">
                                                        {{ $slot->subject?->name ?? 'Subject' }}
                                                        @if($slot->subject?->code)
                                                            <span class="text-gray-500 font-semibold text-[11px]">({{ $slot->subject->code }})</span>
                                                        @endif
                                                    </div>

                                                    {{-- Time --}}
                                                    <div class="text-[11px] text-gray-700 mt-1 font-medium leading-tight">
                                                        {{ $slot->formatted_time_range }}
                                                    </div>

                                                    {{-- Room Number --}}
                                                    <div class="text-[11px] text-gray-500 mt-0.5 leading-tight">
                                                        {{ $slot->room_number ? (str_starts_with(strtolower($slot->room_number), 'room') ? $slot->room_number : 'Room No. ' . $slot->room_number) : 'Room No. -' }}
                                                    </div>

                                                    {{-- Teacher --}}
                                                    <div class="text-[11px] text-gray-600 italic mt-0.5 leading-tight">
                                                        - {{ $slot->teacher?->name ?? 'Demo Teacher' }}
                                                    </div>
                                                </div>

                                                {{-- Edit & Delete Actions (Hidden in Print) --}}
                                                <div class="slot-actions flex items-center gap-0.5 shrink-0 pt-0.5 no-print">
                                                    <button type="button"
                                                            data-id="{{ $slot->id }}"
                                                            data-day="{{ $slot->day }}"
                                                            data-subject_id="{{ $slot->subject_id }}"
                                                            data-staff_id="{{ $slot->staff_id }}"
                                                            data-start_time="{{ $slot->start_time }}"
                                                            data-end_time="{{ $slot->end_time }}"
                                                            data-room_number="{{ $slot->room_number }}"
                                                            onclick="openEditRoutineModal(this.dataset)"
                                                            class="p-1 rounded text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-colors cursor-pointer"
                                                            title="Edit Routine Slot">
                                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('academic.class-timetable.destroy', $slot) }}" class="inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this {{ $slot->subject?->name }} slot on {{ $slot->day }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="p-1 rounded text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors cursor-pointer"
                                                                title="Delete Routine Slot">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach

                                    {{-- Fill empty columns to match max periods --}}
                                    @for($i = $daySlots->count(); $i < $maxSlots; $i++)
                                        <td class="slot-cell border border-gray-200 p-2.5 bg-gray-50/20"></td>
                                    @endfor
                                @else
                                    @for($i = 0; $i < $maxSlots; $i++)
                                        <td class="slot-cell border border-gray-200 p-2.5 bg-gray-50/20"></td>
                                    @endfor
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer Note --}}
            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 no-print flex-wrap gap-2">
                <span>Total Periods Scheduled: <strong class="text-heading font-semibold">{{ $routines->count() }}</strong></span>
                <span class="text-gray-400">EdOrbit Academic Routine Manager</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── ADD NEW ROUTINE MODAL (Pre-selected Class & Section) ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="addRoutineModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 overflow-y-auto no-print">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-visible my-6 border border-gray-100 relative">
            <div class="px-5 py-3.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between rounded-t-2xl">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-heading">Add Routine Slot</h3>
                        <p class="text-[10px] text-gray-400">{{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddRoutineModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('academic.class-timetable.store') }}" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="school_class_id" value="{{ $schoolClass->id }}">
                <input type="hidden" name="section_id" value="{{ $section ? $section->id : '' }}">

                {{-- Subject & Teacher ("sirf us assign class ka subject teacher dikhe") --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Subject:
                        </label>
                        <select name="subject_id" id="add_subject_id" required onchange="filterTeachersForSubject(this.value, 'add')"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Subject</option>
                            @foreach($schoolClass->subjects as $sb)
                                <option value="{{ $sb->id }}">{{ $sb->name }}{{ $sb->code ? ' ('.$sb->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Assigned Teacher:
                        </label>
                        <select name="staff_id" id="add_staff_id"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Teacher (Choose subject first)</option>
                        </select>
                        <p id="add_teacher_note" class="text-[10px] text-gray-400 mt-1">Sirf us subject ke assigned teacher dikhenge.</p>
                    </div>
                </div>

                {{-- Day Selection (Image 1 Format) --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-700 mb-1">
                        <span class="text-red-500">*</span> Day:
                    </label>
                    <div class="relative" id="addDaySelectWrapper">
                        <div id="addDaySelectTrigger" onclick="toggleAddDayDropdown()"
                             class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading flex items-center justify-between cursor-pointer shadow-2xs hover:border-gray-400 select-none">
                            <span id="addDaySelectLabel" class="text-gray-500">Select Day</span>
                            <i data-lucide="chevron-down" id="addDaySelectIcon" class="w-3.5 h-3.5 text-gray-400 transition-transform"></i>
                        </div>

                        <div id="addDayDropdownPanel" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-300 rounded-lg shadow-2xl z-50 p-2.5 text-xs">
                            <div class="mb-2">
                                <input type="text" id="addDaySearchInput" onkeyup="filterAddDaysList(this.value)" placeholder=""
                                       class="w-full border-2 border-blue-600 rounded px-2.5 py-1 text-xs focus:outline-none">
                            </div>
                            <div class="flex items-center justify-between px-2 py-1.5 bg-gray-100 rounded text-[11px] font-semibold text-gray-700 mb-1.5">
                                <button type="button" onclick="selectAllAddDays()" class="text-blue-600 hover:text-blue-800 cursor-pointer font-semibold">Select All</button>
                                <button type="button" onclick="deselectAllAddDays()" class="text-gray-600 hover:text-gray-800 cursor-pointer font-semibold">Deselect All</button>
                            </div>
                            <div id="addDaysList" class="days-dropdown-scroll space-y-0.5" style="max-height: 135px; overflow-y: auto !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayName)
                                    <label class="day-option flex items-center gap-2.5 px-2.5 py-1.5 hover:bg-blue-50/60 rounded cursor-pointer select-none transition-colors">
                                        <input type="checkbox" name="days[]" value="{{ $dayName }}" onchange="updateAddDaySelection()"
                                               class="add-day-checkbox w-3.5 h-3.5 rounded border-gray-300 text-navy focus:ring-0 cursor-pointer">
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

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeAddRoutineModal()" class="px-4 py-2 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-2xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i> Save Routine
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── EDIT ROUTINE SLOT MODAL ── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="editRoutineModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm p-3 overflow-y-auto no-print">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden my-6 border border-gray-100">
            <div class="px-5 py-3.5 bg-gray-50/90 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-heading">Edit Routine Slot</h3>
                        <p class="text-[10px] text-gray-400">Update period timing, room or teacher</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditRoutineModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="" id="editRoutineForm" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                {{-- Day & Subject --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Day:
                        </label>
                        <select name="day" id="edit_day" required
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayName)
                                <option value="{{ $dayName }}">{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Subject:
                        </label>
                        <select name="subject_id" id="edit_subject_id" required onchange="filterTeachersForSubject(this.value, 'edit')"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Subject</option>
                            @foreach($schoolClass->subjects as $sb)
                                <option value="{{ $sb->id }}">{{ $sb->name }}{{ $sb->code ? ' ('.$sb->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Assigned Teacher & Room Number --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Assigned Teacher:
                        </label>
                        <select name="staff_id" id="edit_staff_id"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Teacher</option>
                        </select>
                        <p id="edit_teacher_note" class="text-[10px] text-gray-400 mt-1">Sirf us subject ke assigned teacher dikhenge.</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            Room Number:
                        </label>
                        <input type="text" name="room_number" id="edit_room_number" placeholder="e.g. 101, Lab-1, Library"
                               class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>
                </div>

                {{-- Start Time & End Time --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Start Time:
                        </label>
                        <input type="time" name="start_time" id="edit_start_time" required
                               class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Ending Time:
                        </label>
                        <input type="time" name="end_time" id="edit_end_time" required
                               class="w-full rounded-lg border border-gray-300 text-xs px-2.5 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditRoutineModal()" class="px-4 py-2 text-xs font-semibold border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-2xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i> Update Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for Show View --}}
    <script>
        // Subjects and Teachers dictionary for this class
        const currentClassSubjects = @json($subjectsData);

        function openAddRoutineModal() {
            document.getElementById('addRoutineModal')?.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }

        function closeAddRoutineModal() {
            document.getElementById('addRoutineModal')?.classList.add('hidden');
            closeAddDayDropdown();
        }

        function openEditRoutineModal(slotData) {
            const form = document.getElementById('editRoutineForm');
            if (form) {
                form.action = "{{ url('academic/class-timetable') }}/" + slotData.id;
            }
            document.getElementById('edit_day').value = slotData.day;
            document.getElementById('edit_subject_id').value = slotData.subject_id;
            document.getElementById('edit_start_time').value = slotData.start_time;
            document.getElementById('edit_end_time').value = slotData.end_time;
            document.getElementById('edit_room_number').value = slotData.room_number || '';

            // Filter teachers for this subject and set value
            filterTeachersForSubject(slotData.subject_id, 'edit', slotData.staff_id);

            document.getElementById('editRoutineModal')?.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }

        function closeEditRoutineModal() {
            document.getElementById('editRoutineModal')?.classList.add('hidden');
        }

        // Filter teachers strictly to assigned teachers of that subject
        function filterTeachersForSubject(subjectId, context = 'add', preselectedStaffId = null) {
            const selectId = context === 'add' ? 'add_staff_id' : 'edit_staff_id';
            const noteId = context === 'add' ? 'add_teacher_note' : 'edit_teacher_note';
            const staffSelect = document.getElementById(selectId);
            const note = document.getElementById(noteId);

            if (!staffSelect) return;
            staffSelect.innerHTML = '<option value="">Select Teacher</option>';

            if (!subjectId) return;

            const subject = currentClassSubjects.find(s => s.id == subjectId);
            if (subject && subject.teachers && subject.teachers.length > 0) {
                subject.teachers.forEach(teacher => {
                    const opt = document.createElement('option');
                    opt.value = teacher.id;
                    opt.textContent = teacher.name;
                    if (preselectedStaffId && teacher.id == preselectedStaffId) {
                        opt.selected = true;
                    }
                    staffSelect.appendChild(opt);
                });

                if (!preselectedStaffId && subject.teachers.length === 1) {
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
                    note.textContent = 'Is subject ke liye abhi koi teacher assign nahi hai.';
                    note.className = 'text-[10px] text-amber-600 mt-1';
                }
            }
        }

        // Image 1 Day Selection Dropdown in Modal
        function toggleAddDayDropdown() {
            const panel = document.getElementById('addDayDropdownPanel');
            const icon = document.getElementById('addDaySelectIcon');
            if (!panel) return;
            const isHidden = panel.classList.contains('hidden');
            if (isHidden) {
                panel.classList.remove('hidden');
                icon?.classList.add('rotate-180');
                document.getElementById('addDaySearchInput')?.focus();
            } else {
                closeAddDayDropdown();
            }
        }

        function closeAddDayDropdown() {
            const panel = document.getElementById('addDayDropdownPanel');
            const icon = document.getElementById('addDaySelectIcon');
            panel?.classList.add('hidden');
            icon?.classList.remove('rotate-180');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('addDaySelectWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeAddDayDropdown();
            }
        });

        function filterAddDaysList(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('#addDaysList .day-option').forEach(el => {
                const text = el.querySelector('.day-text')?.textContent.toLowerCase() || '';
                if (text.includes(q)) {
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        function selectAllAddDays() {
            document.querySelectorAll('.add-day-checkbox').forEach(cb => { cb.checked = true; });
            updateAddDaySelection();
        }

        function deselectAllAddDays() {
            document.querySelectorAll('.add-day-checkbox').forEach(cb => { cb.checked = false; });
            updateAddDaySelection();
        }

        function updateAddDaySelection() {
            const checked = Array.from(document.querySelectorAll('.add-day-checkbox:checked')).map(cb => cb.value);
            const label = document.getElementById('addDaySelectLabel');
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

        // Ensure smooth mouse wheel scrolling inside the days list
        document.addEventListener('DOMContentLoaded', function() {
            const addDaysList = document.getElementById('addDaysList');
            if (addDaysList) {
                addDaysList.addEventListener('wheel', function(e) {
                    e.stopPropagation();
                }, { passive: true });
            }
        });
    </script>

@endsection
