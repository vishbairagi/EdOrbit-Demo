@extends('layouts.app')
@section('header', 'My Dashboard')
@section('content')
<div style="max-width:1200px; margin:0 auto;">

    {{-- Profile Card --}}
    <div class="rounded-xl overflow-hidden mb-6" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
        <div class="p-6 flex items-center gap-6" style="background:linear-gradient(135deg,#1a1208,#2d1f0e);">
            @if($person->photo)
                <img src="{{ asset('storage/'.$person->photo) }}" class="w-20 h-20 rounded-full object-cover border-4" style="border-color:#B47A42;">
            @else
                <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold border-4"
                     style="background:linear-gradient(135deg,#B47A42,#D4A055); border-color:rgba(255,255,255,.2);">
                    {{ strtoupper(substr($person->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-bold text-white">{{ $person->name }}</h2>
                <p style="color:#B47A42; font-size:0.875rem;">
                    {{ $type === 'staff' ? ($person->role?->name ?? 'Staff') : ($person->role ?? 'Admin/Principal') }}
                    @if($person->employee_id) • {{ $person->employee_id }} @endif
                </p>
                <p style="color:rgba(232,221,213,0.6); font-size:0.8rem; margin-top:4px;">
                    @if($person->joining_date) Joined {{ $person->joining_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} @endif
                    @if($person->phone) • {{ $person->phone }} @endif
                </p>
            </div>
            <div class="ml-auto flex gap-3">
                <a href="{{ route('staff.id-card') }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                   style="background:rgba(180,122,66,.3); border:1px solid rgba(180,122,66,.5);">
                    🪪 View ID Card
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
            $present = $attendanceSummary['Present'] ?? 0;
            $absent  = $attendanceSummary['Absent']  ?? 0;
            $late    = $attendanceSummary['Late']    ?? 0;
        @endphp
        <div class="rounded-xl p-4 text-center" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="text-2xl font-bold" style="color:#2d5434;">{{ $present }}</div>
            <div class="text-xs mt-1" style="color:#9e8f84;">Present (this month)</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="text-2xl font-bold" style="color:#B4232A;">{{ $absent }}</div>
            <div class="text-xs mt-1" style="color:#9e8f84;">Absent (this month)</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="text-2xl font-bold" style="color:#B47A42;">{{ $pendingLeaves }}</div>
            <div class="text-xs mt-1" style="color:#9e8f84;">Pending Leaves</div>
        </div>
        <div class="rounded-xl p-4 text-center" style="background:#fff; border:1px solid #E8E0D8;">
            <div class="text-2xl font-bold" style="color:#211B17;">{{ $salaryRecords->count() }}</div>
            <div class="text-xs mt-1" style="color:#9e8f84;">Salary Records</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Apply Leave --}}
        <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Apply for Leave
            </div>
            <form method="POST" action="{{ route('staff.leave.apply') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">From Date <span class="text-red-500">*</span></label>
                        <input type="date" name="from_date" class="field-input" required min="{{ today()->toDateString() }}">
                    </div>
                    <div>
                        <label class="field-label">To Date <span class="text-red-500">*</span></label>
                        <input type="date" name="to_date" class="field-input" required min="{{ today()->toDateString() }}">
                    </div>
                </div>
                <div>
                    <label class="field-label">Leave Type</label>
                    <select name="leave_type" class="field-input">
                        <option value="Casual">Casual Leave</option>
                        <option value="Sick">Sick Leave</option>
                        <option value="Earned">Earned Leave</option>
                        <option value="Emergency">Emergency Leave</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Reason <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" class="field-input" placeholder="Briefly explain your reason…" required></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-lg font-semibold text-white"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">Submit Leave Application</button>
            </form>
        </div>

        {{-- Recent Leaves --}}
        <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                My Recent Leaves
            </div>
            <div class="divide-y" style="border-color:#F0EAE3;">
                @forelse($recentLeaves as $leave)
                @php
                    $sc = ['Pending'=>['#fff8e1','#8a6d0b'], 'Processing'=>['#e8f4fd','#1a5276'], 'Approved'=>['#eef7f0','#2d5434'], 'Rejected'=>['#fef0f0','#7a181c']];
                    $c = $sc[$leave->status] ?? ['#f5ece0','#211B17'];
                @endphp
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium" style="color:#211B17;">{{ $leave->leave_type }}</div>
                        <div class="text-xs" style="color:#9e8f84;">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} ({{ $leave->total_days }} day{{ $leave->total_days > 1 ? 's' : '' }})</div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                          style="background:{{ $c[0] }}; color:{{ $c[1] }};">{{ $leave->status }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm" style="color:#9e8f84;">No leave applications yet</div>
                @endforelse
            </div>
        </div>

        {{-- Salary Records --}}
        @if($type === 'staff' && $salaryRecords->count())
        <div class="rounded-xl overflow-hidden lg:col-span-2" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
                Salary Records
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5ece0;">
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Month</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Basic (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Allowances (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Deductions (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Net (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Status</th>
                            <th class="text-center text-sm font-semibold px-4 py-3" style="color:#211B17;">Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaryRecords as $record)
                        <tr style="border-bottom:1px solid #F0EAE3;">
                            <td class="px-4 py-3 text-sm font-semibold" style="color:#211B17;">{{ \Carbon\Carbon::parse($record->month . '-01')->format('F Y') }}</td>
                            <td class="px-4 py-3 text-sm" style="color:#766C63;">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->basic_salary, 2) }}</td>
                            <td class="px-4 py-3 text-sm" style="color:#2d5434;">+{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->allowances, 2) }}</td>
                            <td class="px-4 py-3 text-sm" style="color:#B4232A;">-{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->deductions, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-bold" style="color:#211B17;">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->net_salary, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      style="background:{{ $record->payment_status === 'Paid' ? '#eef7f0' : '#fff8e1' }}; color:{{ $record->payment_status === 'Paid' ? '#2d5434' : '#8a6d0b' }};">
                                    {{ $record->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('sm-administrator.payroll.slip', [session('auth_user_id'), $record->month]) }}" target="_blank"
                                   class="px-2 py-1 rounded-lg text-xs font-medium" style="background:#eef7f0; color:#2d5434;">
                                    Download
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { width:100%; padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; outline:none; font-family:inherit; }
.field-input:focus { border-color:#B47A42; box-shadow:0 0 0 3px rgba(180,122,66,.1); }
textarea.field-input { resize:vertical; }
</style>
@endsection
