<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Certificate – {{ $student->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', serif;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
            min-height: 100vh;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-print { background: #B47A42; color: #fff; }
        .btn-back  { background: #fff; color: #333; border: 1px solid #ddd; }

        .certificate {
            width: 720px;
            background: #fff;
            border: 2px solid #B47A42;
            border-radius: 4px;
            padding: 40px 50px;
            position: relative;
        }
        .certificate::before {
            content: '';
            position: absolute;
            top: 6px; left: 6px; right: 6px; bottom: 6px;
            border: 1px solid #B47A42;
            border-radius: 2px;
            pointer-events: none;
        }

        .cert-header {
            text-align: center;
            border-bottom: 2px solid #B47A42;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .cert-header .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #211B17;
            letter-spacing: 1px;
        }
        .cert-header .school-addr {
            font-size: 13px;
            color: #555;
            margin-top: 4px;
        }
        .cert-header .tc-title {
            font-size: 20px;
            font-weight: bold;
            color: #B47A42;
            margin-top: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .cert-header .tc-no {
            font-size: 12px;
            color: #777;
            margin-top: 4px;
        }

        .cert-body {
            font-size: 14px;
            line-height: 1.9;
            color: #222;
        }

        .cert-row {
            display: flex;
            gap: 0;
            margin-bottom: 6px;
            border-bottom: 1px dotted #ddd;
            padding-bottom: 4px;
        }
        .cert-row .lbl {
            width: 260px;
            font-weight: bold;
            color: #333;
            flex-shrink: 0;
        }
        .cert-row .val {
            flex: 1;
            color: #111;
        }

        .cert-remark {
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.8;
            color: #444;
            text-align: justify;
        }

        .cert-footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .cert-footer .sign-block {
            text-align: center;
        }
        .cert-footer .sign-line {
            width: 160px;
            border-top: 1px solid #333;
            margin: 0 auto 4px;
        }
        .cert-footer .sign-label {
            font-size: 12px;
            color: #555;
        }

        .cert-stamp {
            width: 90px;
            height: 90px;
            border: 2px solid #B47A42;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #B47A42;
            font-weight: bold;
            text-align: center;
            line-height: 1.4;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .certificate { border: 2px solid #B47A42; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">🖨️ Print Certificate</button>
    <button class="btn btn-back" onclick="window.history.back()">← Back</button>
</div>

<div class="certificate">

    <div class="cert-header">
        <div class="school-name">{{ $school?->name ?? 'School Name' }}</div>
        <div class="school-addr">{{ $school?->address ?? '' }}</div>
        <div class="tc-title">Transfer Certificate</div>
        <div class="tc-no">TC No: TC-{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="cert-body">
        <div class="cert-row">
            <div class="lbl">Student Name</div>
            <div class="val">{{ $student->full_name }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Father's Name</div>
            <div class="val">{{ $student->father_name ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Mother's Name</div>
            <div class="val">{{ $student->mother_name ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Date of Birth</div>
            <div class="val">{{ $student->date_of_birth?->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Gender</div>
            <div class="val">{{ $student->gender }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Religion</div>
            <div class="val">{{ $student->religion ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Caste / Category</div>
            <div class="val">{{ $student->caste ?? '—' }} / {{ $student->category ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Nationality</div>
            <div class="val">{{ $student->country ?? 'Indian' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Admission Number</div>
            <div class="val">{{ $student->admission_no }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Class Last Studied</div>
            <div class="val">{{ $student->schoolClass?->name ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Section</div>
            <div class="val">{{ $student->section?->name ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Academic Session</div>
            <div class="val">{{ $student->session?->name ?? '—' }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Date of Leaving</div>
            <div class="val">{{ now()->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</div>
        </div>
        <div class="cert-row">
            <div class="lbl">Reason for Leaving</div>
            <div class="val">Transfer</div>
        </div>
    </div>

    <div class="cert-remark">
        This is to certify that the above named student was a bonafide student of this institution. He/She has
        left the school on {{ now()->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}. His/Her conduct and character were found to be
        <strong>Good</strong> during his/her study in this school.
    </div>

    <div class="cert-footer">
        <div class="sign-block">
            <div class="sign-line"></div>
            <div class="sign-label">Class Teacher</div>
        </div>

        <div class="cert-stamp">SCHOOL<br>SEAL</div>

        <div class="sign-block">
            <div class="sign-line"></div>
            <div class="sign-label">Principal / Head Master</div>
        </div>
    </div>

</div>

</body>
</html>
