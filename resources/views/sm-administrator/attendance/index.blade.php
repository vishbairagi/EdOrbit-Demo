@extends('layouts.app')
@section('header', 'Administrator – Staff Attendance')
@section('content')
<div class="w-full">

    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white flex items-center gap-2"
             style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Staff Attendance
        </div>
        <a href="{{ route('sm-administrator.attendance.report') }}"
           class="px-4 py-2.5 rounded-lg text-sm font-semibold"
           style="background:#f5ece0; color:#211B17; border:1px solid #DED5CB;">
            View Report
        </a>
    </div>

    {{-- Date Picker --}}
    <form method="GET" action="{{ route('sm-administrator.attendance.index') }}" class="mb-5">
        <div class="flex items-center gap-3">
            <label class="text-sm font-semibold" style="color:#211B17;">Select Date:</label>
            <input type="date" name="date" value="{{ $date }}" class="px-4 py-2 rounded-lg border text-sm"
                   style="border-color:#DED5CB; background:#FFFCF7; color:#211B17;" onchange="this.form.submit()">
        </div>
    </form>

    <form method="POST" action="{{ route('sm-administrator.attendance.bulk') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm flex items-center justify-between"
                 style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Attendance for {{ \Carbon\Carbon::parse($date)->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}
                <div class="flex gap-2">
                    <button type="button" onclick="markAll('Present')" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">All Present</button>
                    <button type="button" onclick="markAll('Absent')" class="text-xs px-3 py-1 rounded font-medium" style="background:rgba(255,255,255,.2);">All Absent</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5ece0;">
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">#</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Staff Member</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Designation</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Status</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $i => $member)
                        <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                            <td class="px-4 py-3 text-sm" style="color:#766C63;">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <input type="hidden" name="attendances[{{ $i }}][staff_id]" value="{{ $member->id }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                                         style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm" style="color:#211B17;">{{ $member->name }}</div>
                                        <div class="text-xs" style="color:#9e8f84;">{{ $member->employee_id ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm" style="color:#766C63;">{{ $member->designation ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php $current = $attendances[$member->id]?->status ?? 'Present'; @endphp
                                <select name="attendances[{{ $i }}][status]" class="att-select px-3 py-1.5 rounded-lg text-sm border"
                                        style="border-color:#DED5CB; background:#FFFCF7; color:#211B17; min-width:130px;"
                                        onchange="colorStatus(this)">
                                    @foreach(['Present', 'Absent', 'Late', 'Half Day', 'On Leave'] as $status)
                                    <option value="{{ $status }}" {{ $current === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="attendances[{{ $i }}][note]" placeholder="Optional note"
                                       class="px-3 py-1.5 rounded-lg text-sm border" style="border-color:#DED5CB; background:#FFFCF7; color:#211B17; width:160px;"
                                       value="{{ $attendances[$member->id]?->note ?? '' }}">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-12 text-sm" style="color:#9e8f84;">No active staff found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($staff->count())
            <div class="px-6 py-4 flex justify-end" style="border-top:1px solid #F0EAE3;">
                <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    Save Attendance
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

<style>
.hover-row:hover { background:#FEFAF6; }
</style>
<script>
function markAll(status) {
    document.querySelectorAll('.att-select').forEach(s => {
        s.value = status;
        colorStatus(s);
    });
}
function colorStatus(select) {
    const colors = { Present: '#eef7f0', Absent: '#fef0f0', Late: '#fff8e1', 'Half Day': '#e8f4fd', 'On Leave': '#f0e8f8' };
    const tColors = { Present: '#2d5434', Absent: '#7a181c', Late: '#8a6d0b', 'Half Day': '#1a5276', 'On Leave': '#6a1b9a' };
    select.style.background = colors[select.value] || '#FFFCF7';
    select.style.color = tColors[select.value] || '#211B17';
}
document.querySelectorAll('.att-select').forEach(colorStatus);
</script>
@endsection
