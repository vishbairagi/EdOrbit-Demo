@extends('layouts.app')
@section('header', 'Administrator – Staff Payroll')
@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-5">
        <div class="px-6 py-2.5 rounded-lg font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
            Staff Payroll
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('sm-administrator.payroll.index') }}" class="mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="field-label">Month</label>
            <input type="month" name="month" value="{{ $month }}" class="field-input" style="width:180px;">
        </div>
        <div>
            <label class="field-label">Session</label>
            <select name="session_id" class="field-input" style="width:180px;">
                @foreach($sessions as $session)
                <option value="{{ $session->id }}" {{ $sessionId == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:linear-gradient(135deg,#B47A42,#D4A055);">Load</button>
    </form>

    <form method="POST" action="{{ route('sm-administrator.payroll.generate') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="session_id" value="{{ $sessionId }}">

        <div class="rounded-xl overflow-hidden" style="background:#fff; border:1px solid #E8E0D8; box-shadow:0 2px 8px rgba(33,27,23,.06);">
            <div class="px-6 py-3 font-semibold text-white text-sm" style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                Payroll for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f5ece0;">
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Staff</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Role</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Basic (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Allowances (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Deductions (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Net Salary (₹)</th>
                            <th class="text-left text-sm font-semibold px-4 py-3" style="color:#211B17;">Payment Status</th>
                            <th class="text-center text-sm font-semibold px-4 py-3" style="color:#211B17;">Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $i => $member)
                        @php $record = $records[$member->id] ?? null; @endphp
                        <tr style="border-bottom:1px solid #F0EAE3;" class="hover-row">
                            <td class="px-4 py-3">
                                <div class="font-bold text-sm font-bold" style="color:#211B17;">{{ $member->name }}</div>
                                <div class="text-xs font-bold" style="color:#9e8f84;">{{ $member->employee_id }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold" style="color:#766C63;">{{ $member->role?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[{{ $member->id }}][basic]" min="0" step="0.01"
                                       value="{{ $record?->basic_salary ?? $member->salary ?? 0 }}"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet({{ $member->id }})">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[{{ $member->id }}][allowances]" min="0" step="0.01"
                                       value="{{ $record?->allowances ?? 0 }}"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet({{ $member->id }})">
                            </td>
                            <td class="px-4 py-3">
                                <input type="number" name="staff[{{ $member->id }}][deductions]" min="0" step="0.01"
                                       value="{{ $record?->deductions ?? 0 }}"
                                       class="px-2 py-1 rounded-lg border text-sm text-right font-bold" style="width:100px; border-color:#DED5CB;"
                                       oninput="calcNet({{ $member->id }})">
                            </td>
                            <td class="px-4 py-3">
                                <span id="net-{{ $member->id }}" class="font-bold text-sm font-bold" style="color:#2d5434;">
                                    {{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format(($record?->net_salary ?? ($member->salary ?? 0)), 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <select name="staff[{{ $member->id }}][payment_status]" class="px-2 py-1 rounded-lg border text-sm font-bold" style="border-color:#DED5CB;">
                                    @foreach(['Pending','Paid','Partial'] as $ps)
                                    <option value="{{ $ps }}" {{ ($record?->payment_status ?? 'Pending') === $ps ? 'selected' : '' }}>{{ $ps }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($record)
                                <a href="{{ route('sm-administrator.payroll.slip', [$member, $month]) }}" target="_blank"
                                   class="px-2 py-1 rounded-lg text-xs font-bold" style="background:#eef7f0; color:#2d5434;">
                                    View Slip
                                </a>
                                @else
                                <span class="text-xs font-bold" style="color:#9e8f84;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-12 text-sm font-bold" style="color:#9e8f84;">No active staff found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($staff->count())
            <div class="px-6 py-4 flex justify-end" style="border-top:1px solid #F0EAE3;">
                <button type="submit" class="px-8 py-2.5 rounded-lg font-semibold text-white"
                        style="background:linear-gradient(135deg,#B47A42,#D4A055);">
                    Generate / Update Payroll
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

<style>
.hover-row:hover { background:#FEFAF6; }
.field-label { display:block; font-size:0.8125rem; font-weight:600; color:#211B17; margin-bottom:0.375rem; }
.field-input { padding:0.6rem 0.875rem; border:1px solid #DED5CB; border-radius:8px; font-size:0.9rem; color:#211B17; background:#FFFCF7; outline:none; font-family:inherit; }
</style>

<script>
function calcNet(staffId) {
    const row = document.querySelector(`[name="staff[${staffId}][basic]"]`);
    const rowAllow = document.querySelector(`[name="staff[${staffId}][allowances]"]`);
    const rowDed   = document.querySelector(`[name="staff[${staffId}][deductions]"]`);
    if (!row) return;
    const net = (parseFloat(row.value)||0) + (parseFloat(rowAllow?.value)||0) - (parseFloat(rowDed?.value)||0);
    const netEl = document.getElementById('net-' + staffId);
    if (netEl) netEl.textContent = '₹' + net.toFixed(2);
}
</script>
@endsection
