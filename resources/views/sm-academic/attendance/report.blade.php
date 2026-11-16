@extends('layouts.app')
@section('header', 'Attendance Report')
@section('content')

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableReport, #printableReport * {
                visibility: visible;
            }
            #printableReport {
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
        }
    </style>

    <div class="w-full">
        {{-- Top Header Section --}}
        <div class="flex items-center justify-between mb-5 flex-wrap gap-2 no-print">
            <div>
                <h3 class="text-lg font-bold text-heading flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-navy"></i>
                    Student Attendance Report
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Session: {{ $session->name ?? 'Current Session' }} &nbsp;·&nbsp;
                    Month: {{ $monthCarbon->format('F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- View Attendance Sheet --}}
                <a href="{{ route('academic.attendance.index', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId, 'month' => $monthInput])) }}"
                   class="inline-flex items-center px-3.5 py-1.5 text-xs font-bold rounded-lg border border-gray-300 text-gray-800 bg-white hover:bg-gray-100 transition shadow-2xs gap-1.5 cursor-pointer">
                    <i data-lucide="calendar" class="w-4 h-4 text-navy"></i>
                    <span>View Attendance Sheet</span>
                </a>

                {{-- Take Attendance --}}
                <a href="{{ route('academic.attendance.create', array_filter(['class_id' => $selectedClassId, 'section_id' => $selectedSectionId])) }}"
                   class="inline-flex items-center px-4 py-1.5 text-xs font-bold rounded-lg text-white bg-navy hover:bg-primary-dark transition shadow-2xs gap-1.5 cursor-pointer">
                    <i data-lucide="check-square" class="w-4 h-4 text-amber-400"></i>
                    <span>Take Attendance</span>
                </a>

                @if($selectedClassId && count($reportData) > 0)
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold rounded-lg text-white shadow-2xs cursor-pointer transition hover:opacity-90 bg-emerald-600 hover:bg-emerald-700">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Print Report
                    </button>
                @endif
            </div>
        </div>

        {{-- ══ Filter Toolbar (Project Theme) ══ --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6 no-print">
            <div class="px-5 py-3 border-b border-gray-200 bg-gray-50/70 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs" style="background:#f5ece0; color:#B47A42; border:1px solid #DED5CB;">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-heading">Report Filters</h4>
                        <p class="text-[10px] text-gray-500">Select class, section, and month to generate report</p>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('academic.attendance.report') }}" class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    {{-- Class (No Default Selection) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">
                            <span class="text-red-500">*</span> Class:
                        </label>
                        <select name="class_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Section:</label>
                        <select name="section_id" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                            <option value="">All Sections</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ $selectedSectionId == $s->id ? 'selected' : '' }}>Section {{ $s->name }}</option>
                            @endforeach
                            <option value="none" {{ $selectedSectionId === 'none' ? 'selected' : '' }}>No Section</option>
                        </select>
                    </div>

                    {{-- Month --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Month:</label>
                        <input type="month" name="month" value="{{ $monthInput }}" onchange="this.form.submit()"
                               class="w-full rounded-lg border border-gray-300 text-xs px-3 py-2 bg-white text-heading focus:outline-none focus:ring-1 focus:ring-navy focus:border-navy shadow-2xs">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2 rounded-lg text-white text-xs font-bold bg-navy hover:bg-primary-dark transition shadow-2xs cursor-pointer">
                            Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Report Card Section ══ --}}
        @if(!$selectedClassId)
            {{-- Clean Initial State --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center no-print">
                <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#f5ece0; border:1px solid #DED5CB;">
                    <i data-lucide="bar-chart-2" class="w-6 h-6" style="color:#B47A42;"></i>
                </div>
                <h4 class="text-sm font-bold text-heading">Please Select a Class</h4>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Select a Class and Month from the filters above to generate the monthly attendance summary report.</p>
            </div>
        @else
            {{-- Printable Report Card --}}
            <div id="printableReport" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                {{-- School Header --}}
                <div class="p-4 border-b border-gray-200 text-center bg-gray-50/40">
                    <h2 class="text-base font-black text-heading tracking-wide">{{ $school->name ?? 'EdOrbit Academy' }}</h2>
                    <p class="text-xs font-bold text-navy mt-0.5">Student Monthly Attendance Summary Report</p>
                    <div class="text-[10px] text-gray-500 mt-1">
                        Class: <strong>{{ $selectedClass?->name ?? 'All' }}</strong> &nbsp;|&nbsp;
                        Month: <strong>{{ $monthCarbon->format('F Y') }}</strong> &nbsp;|&nbsp;
                        Generated on: {{ date('d M Y, h:i A') }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-navy text-white text-[10px] uppercase tracking-wider font-semibold">
                                <th class="px-3 py-2.5 w-10 text-center">#</th>
                                <th class="px-3 py-2.5 w-16 text-center">Roll No</th>
                                <th class="px-3.5 py-2.5">Student Name</th>
                                <th class="px-3 py-2.5 text-center">Working Days</th>
                                <th class="px-3 py-2.5 text-center text-emerald-300">Present</th>
                                <th class="px-3 py-2.5 text-center text-red-300">Absent</th>
                                <th class="px-3 py-2.5 text-center text-indigo-300">Late</th>
                                <th class="px-3 py-2.5 text-center text-purple-300">Half Day</th>
                                <th class="px-3 py-2.5 text-center text-amber-300">Holiday</th>
                                <th class="px-4 py-2.5 min-w-[140px] text-right">Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-[11px]">
                            @forelse($reportData as $idx => $r)
                                @php
                                    $stu = $r['student'];
                                    $rate = $r['rate'];
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-3 py-2 text-center text-gray-500 font-medium text-[10px]">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-gray-700">{{ $stu->roll_number ?? '—' }}</td>
                                    <td class="px-3.5 py-2">
                                        <div class="font-bold text-xs text-heading">{{ $stu->full_name }}</div>
                                        <div class="text-[9px] text-gray-400">Adm: {{ $stu->admission_no }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-gray-700">{{ $r['total_working'] }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-emerald-700">{{ $r['present'] }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-red-700">{{ $r['absent'] }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-indigo-700">{{ $r['late'] }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-purple-700">{{ $r['half_day'] }}</td>
                                    <td class="px-3 py-2 text-center font-bold text-amber-700">{{ $r['holiday'] }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full {{ $rate >= 75 ? 'bg-emerald-600' : ($rate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                     style="width: {{ min(100, $rate) }}%"></div>
                                            </div>
                                            <span class="font-black text-xs {{ $rate >= 75 ? 'text-emerald-700' : ($rate >= 50 ? 'text-amber-700' : 'text-red-700') }}">
                                                {{ $rate }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-10 text-xs text-gray-500">
                                        No students found for this class and session.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

@endsection
