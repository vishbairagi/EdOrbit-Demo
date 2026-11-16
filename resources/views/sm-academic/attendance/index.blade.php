@extends('layouts.app')
@section('header', 'Student Attendance')
@section('content')

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableAttendanceSheet, #printableAttendanceSheet * {
                visibility: visible;
            }
            #printableAttendanceSheet {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
                border: none !important;
                box-shadow: none !important;
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
            table {
                font-size: 8px !important;
            }
            th, td {
                padding: 2px 3px !important;
            }
        }
    </style>

    <div class="w-full">
        {{-- Top Header Section --}}
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2 no-print">
            <div>
                <h3 class="text-base font-bold text-heading">Student Attendance</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp;
                    Month: {{ $monthCarbon->format('F Y') }}
                    @if($selectedClass)
                        &nbsp;·&nbsp; Total Students: {{ $students->count() }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Attendance Report Button --}}
                <a href="{{ route('academic.attendance.report', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId, 'month' => $monthInput])) }}"
                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs cursor-pointer">
                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 mr-1 text-gray-600"></i>
                    <span>Attendance Report</span>
                </a>

                {{-- Refresh --}}
                <a href="{{ route('academic.attendance.index') }}"
                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 mr-1 text-gray-600"></i> Refresh
                </a>

                {{-- Take Attendance Button (Primary CTA) --}}
                <a href="{{ route('academic.attendance.create', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId])) }}"
                   class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Take Attendance</span>
                </a>
            </div>
        </div>

        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs no-print"
                 style="background:#eef7f0; color:#2d5434; border:1px solid #b7dfc0;">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-800 hover:text-emerald-950">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-2xs bg-red-50 text-red-700 border border-red-200 no-print">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-950">&times;</button>
            </div>
        @endif

        {{-- ══ Filter Card: Project Theme Style ══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6 no-print">
            {{-- Header Matching Project Theme (Navy + Gold/Warm Tone) --}}
            <div class="px-5 py-3 border-b border-gray-200 bg-gray-50/70 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-heading">Attendance Filters</h4>
                        <p class="text-[10px] text-gray-500">Select class, section, month and Timetable Routine</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('academic.attendance.index') }}" class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    {{-- Class (No default selection!) --}}
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

                    {{-- Month --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Month:
                        </label>
                        <input type="month" name="month" value="{{ $monthInput }}" onchange="this.form.submit()"
                               class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    {{-- Timetable Routine Slot --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-navy">★</span> Timetable Slot:
                        </label>
                        <select name="class_timetable_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">All Routines / Daily Overall</option>
                            @foreach($allTimetableSlots as $slot)
                                <option value="{{ $slot->id }}" {{ $selectedSlotId == $slot->id ? 'selected' : '' }}>
                                    {{ $slot->day }} · {{ $slot->formatted_time_range }} · {{ $slot->subject?->name ?? 'Subject' }} {{ $slot->teacher ? '('.$slot->teacher->name.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-center gap-2">
                    <button type="submit" class="px-6 py-2 rounded-lg text-white text-xs font-bold bg-navy hover:bg-primary-dark transition shadow-2xs cursor-pointer">
                        View Attendance
                    </button>
                    @if(request('class_id') || request('section_id') || request('month') || request('class_timetable_id'))
                        <a href="{{ route('academic.attendance.index') }}" class="px-3 py-2 rounded-lg border border-gray-300 text-xs font-medium text-red-600 hover:bg-gray-50 transition shadow-2xs">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ══ Attendance Table Section (Only shown when Class is Selected) ══ --}}
        @if($selectedClassId && $students->isNotEmpty())
            {{-- Month Info & Print Button --}}
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2 no-print">
                <div>
                    <h4 class="text-sm font-bold text-heading">Month: {{ $monthCarbon->format('F Y') }}</h4>
                    @if($selectedSlotId)
                        @php $activeSlot = $allTimetableSlots->firstWhere('id', $selectedSlotId); @endphp
                        @if($activeSlot)
                            <p class="text-2xs text-navy font-semibold mt-0.5">
                                Filtered Routine: {{ $activeSlot->day }} &nbsp;·&nbsp; {{ $activeSlot->formatted_time_range }} &nbsp;·&nbsp; {{ $activeSlot->subject?->name }} ({{ $activeSlot->teacher?->name ?? 'No Teacher' }})
                            </p>
                        @endif
                    @endif
                </div>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold rounded-lg text-white shadow-2xs cursor-pointer transition hover:opacity-90 bg-emerald-600 hover:bg-emerald-700">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Attendance Sheet
                </button>
            </div>

            {{-- ══ Printable Attendance Sheet Container ══ --}}
            <div id="printableAttendanceSheet" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                {{-- School Header for Print/Screen --}}
                <div class="p-4 border-b border-gray-200 text-center bg-gray-50/40">
                    <h2 class="text-base font-black text-heading tracking-wide">{{ $school->name ?? 'EdOrbit Academy' }}</h2>
                    <div class="text-[10px] text-gray-500 mt-0.5 space-x-2">
                        @if($school->phone)<span>Phone: {{ $school->phone }}</span>@endif
                        @if($school->email)<span>Email: {{ $school->email }}</span>@endif
                        @if($school->address)<span>Address: {{ $school->address }}</span>@endif
                    </div>
                </div>

                {{-- Breadcrumb Sub-Header --}}
                <div class="px-4 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2 text-xs font-semibold bg-gray-50/80 text-gray-700">
                    <div>
                        Attendance - Class: <span class="text-navy font-bold">{{ $selectedClass?->name ?? 'All Classes' }}</span>
                        &nbsp;|&nbsp;
                        Section: <span class="text-navy font-bold">
                            @if($selectedSectionId && $selectedSectionId !== 'none')
                                {{ $sections->firstWhere('id', $selectedSectionId)?->name ?? $selectedSectionId }}
                            @elseif($selectedSectionId === 'none')
                                No Section
                            @else
                                All Sections
                            @endif
                        </span>
                        @if($selectedSlotId)
                            &nbsp;|&nbsp; Routine: <span class="text-navy font-bold">{{ $allTimetableSlots->firstWhere('id', $selectedSlotId)?->formatted_time_range }}</span>
                        @endif
                    </div>
                    <div class="text-[11px] text-gray-600">
                        Month: <strong class="text-heading">{{ $monthCarbon->format('F Y') }}</strong>
                    </div>
                </div>

                {{-- ══ Month Attendance Table (Navy Header) ══ --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200 text-[10px]">
                        <thead>
                            <tr class="bg-navy text-white font-semibold">
                                {{-- Date -> Students --}}
                                <th class="px-2.5 py-2 border border-gray-700 whitespace-nowrap min-w-[140px] text-left">
                                    <div>Date &rarr;</div>
                                    <div class="text-[9px] text-amber-300">Students &darr;</div>
                                </th>

                                {{-- Days 01..31 --}}
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dayDate = $monthCarbon->copy()->day($d);
                                        $isSunday = $dayDate->isSunday();
                                    @endphp
                                    <th class="px-1 py-1 text-center border border-gray-700 w-6 font-bold {{ $isSunday ? 'bg-navy/80 text-amber-300' : '' }}" title="{{ $dayDate->format('l, d M Y') }}">
                                        {{ sprintf('%02d', $d) }}
                                    </th>
                                @endfor

                                {{-- Summary Columns --}}
                                <th class="px-2 py-1 text-center border border-gray-700 whitespace-nowrap font-bold bg-navy/95">Total Attendance</th>
                                <th class="px-2 py-1 text-center border border-gray-700 whitespace-nowrap font-bold text-emerald-400 bg-navy/95">Total Present</th>
                                <th class="px-2 py-1 text-center border border-gray-700 whitespace-nowrap font-bold text-red-400 bg-navy/95">Total Absent</th>
                                <th class="px-2 py-1 text-center border border-gray-700 whitespace-nowrap font-bold text-amber-300 bg-navy/95">Total Holiday</th>
                                <th class="px-2 py-1 text-center border border-gray-700 whitespace-nowrap font-bold text-indigo-300 bg-navy/95">Total Late</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($students as $stu)
                                @php
                                    $summary = $studentSummary[$stu->id] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'holiday' => 0, 'late' => 0];
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    {{-- Student Info --}}
                                    <td class="px-2.5 py-1.5 border border-gray-200 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 leading-tight">{{ $stu->full_name }}</div>
                                        <div class="text-[9px] text-gray-500">Roll No. {{ $stu->roll_number ?? '—' }} &nbsp;·&nbsp; {{ $stu->admission_no }}</div>
                                    </td>

                                    {{-- Days 01..31 --}}
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $attRecord = $matrix[$stu->id][$d] ?? null;
                                            $dayDate = $monthCarbon->copy()->day($d);
                                            $isSunday = $dayDate->isSunday();
                                        @endphp
                                        <td class="px-1 py-1 text-center border border-gray-200 font-bold {{ $isSunday && !$attRecord ? 'bg-amber-50/50' : '' }}"
                                            title="{{ $stu->full_name }} - {{ $dayDate->format('d M') }}: {{ $attRecord ? $attRecord->status : ($isSunday ? 'Sunday / Holiday' : 'Not Marked') }}">
                                            @if($attRecord)
                                                @if($attRecord->status === 'Present')
                                                    <span class="text-emerald-700">P</span>
                                                @elseif($attRecord->status === 'Absent')
                                                    <span class="text-red-700">A</span>
                                                @elseif($attRecord->status === 'Holiday')
                                                    <span class="text-amber-700">H</span>
                                                @elseif($attRecord->status === 'Late')
                                                    <span class="text-indigo-700">L</span>
                                                @elseif($attRecord->status === 'Half Day')
                                                    <span class="text-purple-700">HD</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            @elseif($isSunday)
                                                <span class="text-amber-500 font-medium">H</span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endfor

                                    {{-- Summary Counts --}}
                                    <td class="px-2 py-1 text-center border border-gray-200 font-bold text-gray-700 bg-gray-50/60">
                                        {{ $summary['total'] }}
                                    </td>
                                    <td class="px-2 py-1 text-center border border-gray-200 font-bold text-emerald-700 bg-emerald-50/30">
                                        {{ $summary['present'] }}
                                    </td>
                                    <td class="px-2 py-1 text-center border border-gray-200 font-bold text-red-700 bg-red-50/30">
                                        {{ $summary['absent'] }}
                                    </td>
                                    <td class="px-2 py-1 text-center border border-gray-200 font-bold text-amber-700 bg-amber-50/30">
                                        {{ $summary['holiday'] }}
                                    </td>
                                    <td class="px-2 py-1 text-center border border-gray-200 font-bold text-indigo-700 bg-indigo-50/30">
                                        {{ $summary['late'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend Footer --}}
                <div class="px-4 py-2.5 border-t border-gray-200 bg-gray-50/60 flex flex-wrap items-center justify-between gap-3 text-[10px] text-gray-600">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-bold text-gray-700">Legend:</span>
                        <span class="inline-flex items-center gap-1"><strong class="text-emerald-700 font-black">P</strong> = Present</span>
                        <span class="inline-flex items-center gap-1"><strong class="text-red-700 font-black">A</strong> = Absent</span>
                        <span class="inline-flex items-center gap-1"><strong class="text-amber-700 font-black">H</strong> = Holiday / Weekend</span>
                        <span class="inline-flex items-center gap-1"><strong class="text-indigo-700 font-black">L</strong> = Late</span>
                        <span class="inline-flex items-center gap-1"><strong class="text-purple-700 font-black">HD</strong> = Half Day</span>
                    </div>
                    <div>
                        Total Students Enrolled: <strong class="text-navy font-bold">{{ $students->count() }}</strong>
                    </div>
                </div>
            </div>
        @elseif($selectedClassId)
            {{-- Class selected but no students --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center no-print">
                <i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                <h4 class="text-sm font-bold text-gray-800">No active students found in this class &amp; section</h4>
                <p class="text-xs text-gray-400 mt-1">Please select another class or enroll students first.</p>
            </div>
        @else
            {{-- Clean Initial State (No Class Selected) --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center no-print">
                <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f5ece0; border:1px solid #DED5CB;">
                    <i data-lucide="calendar" class="w-6 h-6" style="color:#B47A42;"></i>
                </div>
                <h4 class="text-sm font-bold text-heading">Please Select a Class</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Select a Class, Section and Month in the filter above, then click <strong>View Attendance</strong> to load the monthly attendance sheet.</p>
            </div>
        @endif
    </div>

@endsection
