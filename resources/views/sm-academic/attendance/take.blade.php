@extends('layouts.app')
@section('header', 'Take Attendance')
@section('content')

    <style>
        /* Scoped Attendance Status Pill Styles with Guaranteed Active Highlighting */
        .status-btn-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .status-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #d1d5db;
            color: #374151;
            background-color: #ffffff;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            outline: none;
        }

        .status-btn:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        /* Solid Active States with Guaranteed Contrast */
        .status-btn.is-active-Present {
            background-color: #16a34a !important;
            border-color: #15803d !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(22, 163, 74, 0.35) !important;
        }

        .status-btn.is-active-Absent {
            background-color: #dc2626 !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.35) !important;
        }

        .status-btn.is-active-Late {
            background-color: #4f46e5 !important;
            border-color: #4338ca !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.35) !important;
        }

        .status-btn.is-active-HalfDay {
            background-color: #9333ea !important;
            border-color: #7e22ce !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(147, 51, 234, 0.35) !important;
        }

        .status-btn.is-active-Holiday {
            background-color: #d97706 !important;
            border-color: #b45309 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(217, 119, 6, 0.35) !important;
        }
    </style>

    <div class="w-full">
        {{-- Top Header Section --}}
        <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
            <div>
                <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                    <i data-lucide="check-square" class="w-5 h-5 text-navy"></i>
                    Take Class Attendance (Timetable-Based)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp;
                    Day: <strong class="text-navy">{{ $dayName }}</strong> &nbsp;·&nbsp;
                    Date: {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Prominent Attendance Report Button --}}
                <a href="{{ route('academic.attendance.report', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId])) }}"
                   class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-lg border border-[#DED5CB] bg-white text-[#211B17] hover:bg-[#f5ece0] transition shadow-2xs gap-1.5 cursor-pointer">
                    <i data-lucide="bar-chart-2" class="w-4 h-4" style="color: #B47A42;"></i>
                    <span>Attendance Report</span>
                </a>

                {{-- View Monthly Attendance Sheet --}}
                <a href="{{ route('academic.attendance.index', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId])) }}"
                   class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-lg border border-gray-300 text-gray-800 bg-white hover:bg-gray-100 transition shadow-2xs gap-1.5 cursor-pointer">
                    <i data-lucide="calendar" class="w-4 h-4 text-navy"></i>
                    <span>View Attendance Sheet</span>
                </a>
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

        {{-- ══ Timetable-Based Attendance Filter Selection (Project Theme) ══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-gray-200 bg-gray-50/70 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-heading">Select Class &amp; Timetable Routine Slot</h4>
                        <p class="text-[10px] text-gray-500">Pick date and class to view routine slots and students</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                    Day: {{ $dayName }}
                </span>
            </div>

            <form method="GET" action="{{ route('academic.attendance.create') }}" id="attendanceFilterForm" class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    {{-- Date --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Date:
                        </label>
                        <input type="date" name="date" id="attendanceDateInput" value="{{ $date }}" onchange="handleDateChange()"
                               class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    {{-- Class (No Default Selection!) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Class:
                        </label>
                        <select name="class_id" id="filter_class_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Section:
                        </label>
                        <select name="section_id" id="filter_section_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">All Sections</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ $selectedSectionId == $s->id ? 'selected' : '' }}>
                                    Section {{ $s->name }}
                                </option>
                            @endforeach
                            <option value="none" {{ $selectedSectionId === 'none' ? 'selected' : '' }}>No Section / General</option>
                        </select>
                    </div>

                    {{-- Timetable Routine Slot for that Day (with Bulk All Slots Option) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-navy">★</span> Timetable Period Slot:
                        </label>
                        <select name="class_timetable_id" id="class_timetable_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="all" {{ ($selectedSlotId === 'all' || !$selectedSlotId) ? 'selected' : '' }}>
                                ★ All Scheduled Routine Slots of the Day (Bulk All Slots)
                            </option>
                            @if($timetableSlots->isNotEmpty())
                                @foreach($timetableSlots as $slot)
                                    <option value="{{ $slot->id }}" {{ $selectedSlotId == $slot->id ? 'selected' : '' }}>
                                        {{ $slot->formatted_time_range }} · {{ $slot->subject?->name ?? 'Subject' }} {{ $slot->teacher ? '('.$slot->teacher->name.')' : '' }} {{ $slot->room_number ? '[Room '.$slot->room_number.']' : '' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Attendance Marking Card ══ --}}
        @if($selectedClassId && $students->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                {{-- Toolbar with Details and Mark All --}}
                <div class="p-4 border-b border-gray-200 bg-gray-50/70 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-heading">
                            Class: {{ $selectedClass?->name }}
                            @if($selectedSectionId && $selectedSectionId !== 'none')
                                &nbsp;·&nbsp; Section: {{ $sections->firstWhere('id', $selectedSectionId)?->name }}
                            @endif
                        </span>

                        @if($selectedSlot)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                <i data-lucide="clock" class="w-3 h-3 text-amber-700"></i>
                                {{ $selectedSlot->formatted_time_range }}
                                &nbsp;·&nbsp; {{ $selectedSlot->subject?->name }}
                                @if($selectedSlot->teacher)
                                    ({{ $selectedSlot->teacher->name }})
                                @endif
                                @if($selectedSlot->room_number)
                                    · Room {{ $selectedSlot->room_number }}
                                @endif
                            </span>
                        @elseif($selectedSlotId === 'all')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold text-navy" style="background:#f5ece0; border:1px solid #DED5CB;">
                                <i data-lucide="zap" class="w-3 h-3 text-amber-600"></i>
                                Bulk Mode: All Slots of {{ $dayName }} ({{ $timetableSlots->count() }} routine periods)
                            </span>
                        @endif

                        @if($alreadyMarked)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i>
                                Already Marked (Editing Mode)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                <i data-lucide="info" class="w-3 h-3 text-sky-600"></i>
                                New Entry
                            </span>
                        @endif
                    </div>

                    {{-- Quick Batch Actions (Mark All) --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] font-bold text-gray-700 mr-1">Mark All:</span>
                        <button type="button" onclick="setAllStatus('Present')" class="px-3 py-1 text-xs font-bold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-2xs cursor-pointer">
                            Present
                        </button>
                        <button type="button" onclick="setAllStatus('Absent')" class="px-3 py-1 text-xs font-bold rounded-lg text-white bg-red-600 hover:bg-red-700 transition shadow-2xs cursor-pointer">
                            Absent
                        </button>
                        <button type="button" onclick="setAllStatus('Late')" class="px-3 py-1 text-xs font-bold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-2xs cursor-pointer">
                            Late
                        </button>
                        <button type="button" onclick="setAllStatus('Half Day')" class="px-3 py-1 text-xs font-bold rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition shadow-2xs cursor-pointer">
                            Half Day
                        </button>
                        <button type="button" onclick="setAllStatus('Holiday')" class="px-3 py-1 text-xs font-bold rounded-lg text-white bg-amber-600 hover:bg-amber-700 transition shadow-2xs cursor-pointer">
                            Holiday
                        </button>
                    </div>
                </div>

                {{-- Bulk All Slots Banner Notice --}}
                @if($selectedSlotId === 'all')
                    <div class="px-5 py-2.5 border-b border-[#DED5CB] flex items-center justify-between text-xs" style="background:#faf6f0; color:#4a3b32;">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-[#B47A42]"></i>
                            <span>
                                <strong>Bulk All Slots Option Active:</strong> Attendance marked below will be saved for 
                                @if($timetableSlots->isNotEmpty())
                                    <strong>all {{ $timetableSlots->count() }} timetable slots</strong> of {{ $dayName }}
                                    ({{ $timetableSlots->map(fn($s) => $s->formatted_time_range . ' ' . ($s->subject?->name ?? ''))->implode(', ') }})
                                @else
                                    the whole day
                                @endif
                                in a single submission!
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Attendance Form --}}
                <form method="POST" action="{{ route('academic.attendance.store') }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <input type="hidden" name="school_class_id" value="{{ $selectedClassId }}">
                    <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
                    <input type="hidden" name="class_timetable_id" value="{{ $selectedSlotId }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-navy text-white text-[10px] uppercase tracking-wider font-semibold">
                                    <th class="px-3 py-2.5 w-12 text-center">#</th>
                                    <th class="px-3 py-2.5 w-20 text-center">Roll No</th>
                                    <th class="px-3.5 py-2.5">Student Name</th>
                                    <th class="px-3 py-2.5 whitespace-nowrap">Admission No</th>
                                    <th class="px-4 py-2.5 min-w-[340px]">Attendance Status</th>
                                    <th class="px-3 py-2.5 min-w-[180px]">Note / Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($students as $idx => $stu)
                                    @php
                                        $existing = $existingRecords->get($stu->id);
                                        $currentStatus = $existing ? $existing->status : 'Present';
                                        $currentNote = $existing ? $existing->note : '';
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-3 py-2 text-center text-gray-500 font-medium text-[10px] align-middle">
                                            {{ $idx + 1 }}
                                            <input type="hidden" name="attendances[{{ $idx }}][student_id]" value="{{ $stu->id }}">
                                        </td>
                                        <td class="px-3 py-2 text-center font-bold text-gray-700 align-middle">
                                            {{ $stu->roll_number ?? '—' }}
                                        </td>
                                        <td class="px-3.5 py-2 align-middle">
                                            <div class="font-bold text-xs text-heading flex items-center gap-2">
                                                <span>{{ $stu->full_name }}</span>
                                                @if($stu->gender)
                                                    <span class="text-[9px] font-semibold px-1 py-0.2 rounded bg-gray-100 text-gray-600">
                                                        {{ substr($stu->gender, 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-gray-500 font-mono text-[11px] align-middle whitespace-nowrap">
                                            {{ $stu->admission_no }}
                                        </td>
                                        <td class="px-4 py-2 align-middle">
                                            <div class="status-btn-group" data-row="{{ $idx }}">
                                                {{-- Hidden Input containing status --}}
                                                <input type="hidden" name="attendances[{{ $idx }}][status]" id="status_val_{{ $idx }}" value="{{ $currentStatus }}">

                                                {{-- Present --}}
                                                <button type="button" 
                                                        onclick="setStatus({{ $idx }}, 'Present')" 
                                                        class="status-btn {{ $currentStatus === 'Present' ? 'is-active-Present' : '' }}" 
                                                        data-status="Present">
                                                    Present
                                                </button>

                                                {{-- Absent --}}
                                                <button type="button" 
                                                        onclick="setStatus({{ $idx }}, 'Absent')" 
                                                        class="status-btn {{ $currentStatus === 'Absent' ? 'is-active-Absent' : '' }}" 
                                                        data-status="Absent">
                                                    Absent
                                                </button>

                                                {{-- Late --}}
                                                <button type="button" 
                                                        onclick="setStatus({{ $idx }}, 'Late')" 
                                                        class="status-btn {{ $currentStatus === 'Late' ? 'is-active-Late' : '' }}" 
                                                        data-status="Late">
                                                    Late
                                                </button>

                                                {{-- Half Day --}}
                                                <button type="button" 
                                                        onclick="setStatus({{ $idx }}, 'Half Day')" 
                                                        class="status-btn {{ $currentStatus === 'Half Day' ? 'is-active-HalfDay' : '' }}" 
                                                        data-status="Half Day">
                                                    Half Day
                                                </button>

                                                {{-- Holiday --}}
                                                <button type="button" 
                                                        onclick="setStatus({{ $idx }}, 'Holiday')" 
                                                        class="status-btn {{ $currentStatus === 'Holiday' ? 'is-active-Holiday' : '' }}" 
                                                        data-status="Holiday">
                                                    Holiday
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-middle">
                                            <input type="text" name="attendances[{{ $idx }}][note]" value="{{ $currentNote }}" placeholder="Optional note / reason..."
                                                   class="w-full text-xs px-2.5 py-1 rounded border border-gray-200 focus:outline-none focus:border-navy bg-white">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Sticky Submit Bar --}}
                    <div class="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs text-gray-500 font-medium">Total Students: <strong class="text-gray-800">{{ $students->count() }}</strong></span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('academic.attendance.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition shadow-2xs">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center gap-1.5 px-6 py-2 rounded-lg text-xs font-bold text-white bg-navy hover:bg-primary-dark transition shadow-2xs cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i> Save Attendance
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @elseif($selectedClassId)
            {{-- Class selected but no students --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                <h4 class="text-sm font-bold text-gray-800">No active students found in this class &amp; section</h4>
                <p class="text-xs text-gray-400 mt-1">Please add students to this class in Student Management first.</p>
            </div>
        @else
            {{-- Initial state: No Class Selected --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f5ece0; border:1px solid #DED5CB;">
                    <i data-lucide="calendar" class="w-6 h-6" style="color:#B47A42;"></i>
                </div>
                <h4 class="text-sm font-bold text-heading">Please Select a Class</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Select a Class from the filter dropdown above to load routine timetable slots and students for attendance.</p>
            </div>
        @endif
    </div>

    <script>
        const STATUS_CLASSES = {
            'Present': 'is-active-Present',
            'Absent': 'is-active-Absent',
            'Late': 'is-active-Late',
            'Half Day': 'is-active-HalfDay',
            'Holiday': 'is-active-Holiday'
        };

        const ALL_ACTIVE_CLASSES = Object.values(STATUS_CLASSES);

        function setStatus(rowIdx, status) {
            const input = document.getElementById(`status_val_${rowIdx}`);
            if (input) input.value = status;

            const group = document.querySelector(`.status-btn-group[data-row="${rowIdx}"]`);
            if (!group) return;

            group.querySelectorAll('.status-btn').forEach(btn => {
                ALL_ACTIVE_CLASSES.forEach(cls => btn.classList.remove(cls));
                if (btn.getAttribute('data-status') === status) {
                    btn.classList.add(STATUS_CLASSES[status]);
                }
            });
        }

        function setAllStatus(status) {
            const targetClass = STATUS_CLASSES[status];
            if (!targetClass) return;

            document.querySelectorAll('.status-btn-group').forEach(group => {
                const rowIdx = group.getAttribute('data-row');
                const input = document.getElementById(`status_val_${rowIdx}`);
                if (input) input.value = status;

                group.querySelectorAll('.status-btn').forEach(btn => {
                    ALL_ACTIVE_CLASSES.forEach(cls => btn.classList.remove(cls));
                    if (btn.getAttribute('data-status') === status) {
                        btn.classList.add(targetClass);
                    }
                });
            });
        }

        function handleDateChange() {
            document.getElementById('attendanceFilterForm').submit();
        }
    </script>

@endsection
