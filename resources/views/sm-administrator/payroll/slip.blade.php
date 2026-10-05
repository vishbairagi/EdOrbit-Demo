<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Salary Slip – {{ $staff->name }} – {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { margin: 12mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #f5f5f5; padding: 24px; color: #211B17; }
        .slip { background: #fff; max-width: 700px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
        .slip-header { background: linear-gradient(135deg, #1a1208, #2d1f0e); color: #fff; padding: 24px; text-align: center; }
        .school-name { font-size: 1.25rem; font-weight: 800; color: #D4A055; }
        .slip-title { font-size: 0.8rem; color: rgba(232,221,213,.6); margin-top: 4px; letter-spacing: 0.08em; text-transform: uppercase; }
        .month-badge { display: inline-block; margin-top: 10px; padding: 4px 16px; border-radius: 20px; background: rgba(180,122,66,.3); color: #D4A055; font-size: 0.8rem; font-weight: 600; }

        .slip-body { padding: 24px; }
        .section-title { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #B47A42; margin-bottom: 10px; padding-bottom: 4px; border-bottom: 1px solid #E8E0D8; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 20px; }
        .info-item { font-size: 0.82rem; }
        .info-label { color: #9e8f84; }
        .info-value { color: #211B17; font-weight: 600; }

        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 0.85rem; }
        .salary-table th { background: #f5ece0; padding: 8px 12px; text-align: left; color: #211B17; font-weight: 600; }
        .salary-table td { padding: 8px 12px; border-bottom: 1px solid #F0EAE3; color: #766C63; }
        .salary-table .amount { text-align: right; }

        .net-row { background: linear-gradient(135deg, #B47A42, #D4A055); }
        .net-row td { color: #fff !important; font-weight: 700; font-size: 1rem; padding: 12px 16px; }

        .footer-note { font-size: 0.7rem; color: #9e8f84; text-align: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid #E8E0D8; }

        .no-print { margin-bottom: 20px; display: flex; gap: 12px; }
        @media print { .no-print { display: none !important; } body { background: #fff; padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:10px 24px; background:linear-gradient(135deg,#B47A42,#D4A055); color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-family:'Outfit',sans-serif;">🖨 Print / Download</button>
        <button onclick="window.close()" style="padding:10px 24px; background:#f5ece0; color:#211B17; border:1px solid #DED5CB; border-radius:8px; font-weight:600; cursor:pointer; font-family:'Outfit',sans-serif;">Close</button>
    </div>

    @php $school = \App\Models\School::find($staff->school_id); @endphp
    <div class="slip">
        <div class="slip-header">
            <div class="school-name">{{ $school?->name ?? 'School Name' }}</div>
            <div class="slip-title">Staff Salary Slip</div>
            <div class="month-badge">{{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</div>
        </div>
        <div class="slip-body">
            <div class="section-title">Employee Details</div>
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Name</div><div class="info-value">{{ $staff->name }}</div></div>
                <div class="info-item"><div class="info-label">Employee ID</div><div class="info-value">{{ $staff->employee_id ?? 'N/A' }}</div></div>
                <div class="info-item"><div class="info-label">Designation</div><div class="info-value">{{ $staff->designation ?? '—' }}</div></div>
                <div class="info-item"><div class="info-label">Role</div><div class="info-value">{{ $staff->role?->name ?? '—' }}</div></div>
                <div class="info-item"><div class="info-label">Joining Date</div><div class="info-value">{{ $staff->joining_date?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) ?? '—' }}</div></div>
                <div class="info-item"><div class="info-label">Payment Status</div><div class="info-value" style="color:{{ $record->payment_status === 'Paid' ? '#2d5434' : '#B4232A' }};">{{ $record->payment_status }}</div></div>
            </div>

            <div class="section-title">Salary Details</div>
            <table class="salary-table">
                <thead>
                    <tr><th>Component</th><th class="amount">Amount (₹)</th></tr>
                </thead>
                <tbody>
                    <tr><td>Basic Salary</td><td class="amount">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->basic_salary, 2) }}</td></tr>
                    <tr><td style="color:#2d5434;">+ Allowances</td><td class="amount" style="color:#2d5434;">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->allowances, 2) }}</td></tr>
                    <tr><td style="color:#B4232A;">- Deductions</td><td class="amount" style="color:#B4232A;">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->deductions, 2) }}</td></tr>
                </tbody>
                <tfoot>
                    <tr class="net-row"><td>Net Salary</td><td class="amount">{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format($record->net_salary, 2) }}</td></tr>
                </tfoot>
            </table>

            @if($record->note)
            <div class="section-title">Remarks</div>
            <p style="font-size:0.82rem; color:#766C63; margin-bottom:16px;">{{ $record->note }}</p>
            @endif

            <div class="footer-note">
                This is a computer-generated salary slip and does not require a physical signature.<br>
                Generated on {{ now()->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }} by {{ $school?->name ?? 'School Management System' }}.
            </div>
        </div>
    </div>
</body>
</html>
