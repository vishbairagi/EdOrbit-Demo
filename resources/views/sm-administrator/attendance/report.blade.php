@extends('layouts.app')
@section('header', 'Staff Attendance Report')
@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Attendance Report
        </div>
        <a href="{{ route('sm-administrator.attendance.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">← Mark Attendance</a>
    </div>

    <form method="GET" action="{{ route('sm-administrator.attendance.report') }}" class="mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="field-label">Month</label>
            <input type="month" name="month" value="{{ $month }}" class="field-input" style="width:180px;">
        </div>
        <div>
            <label class="field-label">Staff</label>
            <select name="staff_id" class="field-input" style="width:200px;">
                <option value="">All Staff</option>
                @foreach($staff as $member)
                <option value="{{ $member->id }}" {{ $staffId == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Load Report</button>
    </form>

    @php
        $selectedMonth = \Carbon\Carbon::parse($month);
        $daysInMonth = $selectedMonth->daysInMonth;
    @endphp

    <div class="flex justify-center gap-3 mb-4">
        <button onclick="window.print()" class="px-5 py-2 rounded-lg text-sm font-semibold text-white shadow" style="background:#28a745;">Print Staff Attendance Sheet</button>
    </div>

    <div class="rounded-xl overflow-hidden mb-4" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        <div class="text-center py-4" style="border-bottom:1px solid #E8E0D8;">
            <h2 class="text-xl font-bold" style="color:#211B17;">EdOrbit</h2>
            <div class="text-xs mt-1" style="color:#9e8f84;">Staff Attendance Report</div>
        </div>
        <div class="overflow-x-auto">
            <table style="width:100%; border-collapse:collapse; white-space:nowrap;">
                <thead>
                    <tr style="background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff;">
                        <th class="text-left text-sm font-semibold px-4 py-2 border border-[#D4A055]" rowspan="2">
                            Staff Attendance<br>
                            <span class="text-xs font-normal">Month: {{ $selectedMonth->format('F Y') }}</span>
                        </th>
                        <th class="text-center text-sm font-semibold px-2 py-2 border border-[#D4A055]" colspan="{{ $daysInMonth }}">Date &rarr;</th>
                        <th class="text-center text-sm font-semibold px-3 py-2 border border-[#D4A055]" colspan="4">Total</th>
                    </tr>
                    <tr style="background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff;">
                        @for($d=1; $d<=$daysInMonth; $d++)
                        <th class="text-center text-xs px-1 py-1 border border-[#D4A055]">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</th>
                        @endfor
                        <th class="text-center text-xs px-2 py-1 border border-[#D4A055]">Attendance</th>
                        <th class="text-center text-xs px-2 py-1 border border-[#D4A055]">Present</th>
                        <th class="text-center text-xs px-2 py-1 border border-[#D4A055]">Absent</th>
                        <th class="text-center text-xs px-2 py-1 border border-[#D4A055]">Holiday</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                    @if(!$staffId || $staffId == $member->id)
                    @php 
                        $memberAttendances = $attendances[$member->id] ?? collect(); 
                        
                        $present = $memberAttendances->where('status', 'Present')->count();
                        $absent = $memberAttendances->where('status', 'Absent')->count();
                        $halfDay = $memberAttendances->where('status', 'Half Day')->count();
                        $late = $memberAttendances->where('status', 'Late')->count();
                        $leave = $memberAttendances->where('status', 'On Leave')->count();

                        $totalAtt = $present + $late + $halfDay; 
                        $totalHoliday = $halfDay + $leave; 
                    @endphp
                    <tr class="hover-row" style="border-bottom:1px solid #E8E0D8;">
                        <td class="px-4 py-2 text-sm border-r border-[#E8E0D8]">
                            <div class="font-semibold" style="color:#211B17;">{{ $member->name }}</div>
                            <div class="text-xs" style="color:#9e8f84;">{{ $member->phone ?? 'N/A' }}</div>
                        </td>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dateStr = $selectedMonth->format('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
                                $att = $memberAttendances->first(function($val) use ($dateStr) {
                                    return $val->date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) === $dateStr;
                                });
                                $char = '';
                                $color = '';
                                if($att) {
                                    if($att->status == 'Present') { $char = 'P'; $color = '#28a745'; }
                                    elseif($att->status == 'Absent') { $char = 'A'; $color = '#dc3545'; }
                                    elseif($att->status == 'Late') { $char = 'L'; $color = '#fd7e14'; }
                                    elseif($att->status == 'Half Day') { $char = 'H'; $color = '#ffc107'; }
                                    elseif($att->status == 'On Leave') { $char = 'V'; $color = '#6f42c1'; }
                                }
                            @endphp
                            <td class="text-center text-sm font-bold border-r border-[#E8E0D8]" style="color:{{ $color }}; min-width:28px;">
                                {{ $char }}
                            </td>
                        @endfor
                        <td class="text-center text-sm font-semibold border-r border-[#E8E0D8]" style="color:#211B17;">{{ $totalAtt }}</td>
                        <td class="text-center text-sm font-semibold border-r border-[#E8E0D8]" style="color:#28a745;">{{ $present }}</td>
                        <td class="text-center text-sm font-semibold border-r border-[#E8E0D8]" style="color:#dc3545;">{{ $absent }}</td>
                        <td class="text-center text-sm font-semibold" style="color:#ffc107;">{{ $totalHoliday }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; outline:none; font-family:inherit; }
</style>
@endsection
