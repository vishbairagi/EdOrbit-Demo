<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} · Admit Cards</title>
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { margin: 0; }
        body { font-family: sans-serif; font-size: 13px; margin: 0; padding: 0; color: #111; background: #f3f4f6; }
        
        .toolbar {
            text-align: right; margin-bottom: 20px; max-width: 800px; margin: 20px auto 0 auto; display: flex; justify-content: flex-end; gap: 10px;
        }
        .toolbar button {
            padding: 8px 16px; background: #000; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-family: sans-serif;
        }

        .admit-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            page-break-after: always; break-after: page; margin-bottom: 30px;
            position: relative;
            min-height: 95vh;
        }
        .admit-container.has-bg > img.bg-print-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; opacity: 0.85; }
        .admit-container > * { position: relative; z-index: 1; }
        .admit-container:last-of-type { page-break-after: auto; break-after: auto; margin-bottom: 0; }

        .header { text-align: center; margin-bottom: 25px; line-height: 1.4; }
        .header h1 { font-size: 20px; font-weight: bold; margin: 0 0 5px 0; }
        .header p { margin: 2px 0; font-size: 11px; }

        .title { text-align: center; margin-bottom: 25px; }
        .title span { font-size: 16px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }

        .student-info-wrapper { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .student-info { width: 100%; font-size: 13px; line-height: 2.2; }
        .student-info table { width: 100%; }
        .student-info td.label { font-weight: bold; width: 110px; }
        .student-info td { vertical-align: middle; }

        .photo { width: 90px; height: 110px; border: 1px solid #111; object-fit: cover; margin-left: 20px; display: block; }

        .exam-info { margin-bottom: 10px; font-weight: bold; font-size: 13px; }

        table.papers-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; font-size: 13px; }
        table.papers-table th, table.papers-table td { border: 1px solid #111; padding: 8px 10px; text-align: left; }
        table.papers-table th { font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: right; }
        .footer .signature-line { display: inline-block; border-bottom: 1px solid #111; width: 150px; margin-bottom: 5px; }
        .footer .auth-by { font-weight: bold; font-size: 13px; margin-right: 25px; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; font-size: 16px; }
            .header h1 { font-size: 24px; }
            .header p { font-size: 14px; }
            .title span { font-size: 18px; }
            .student-info, .exam-info { font-size: 14px; }
            table.papers-table th, table.papers-table td { padding: 10px; font-size: 14px; }
            .admit-container { max-width: 100%; margin: 0; padding: 30px; box-shadow: none; border: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.history.back()" style="background: #e5e7eb; color: #374151;">Cancel</button>
        <button type="button" onclick="window.print()">Print Admit Cards</button>
    </div>

    @php
        $school = $exam->school;
        $session = $exam->academicSession ?? \App\Models\AcademicSession::find(session('selected_academic_session_id'));
        $bg = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'examination_background');
        $bgClass = $bg ? 'has-bg' : '';
        $showPhoto = \App\Models\SchoolSetting::getSetting($school->id, $session->id, 'student_admit_card_photo', '0') == '1';
    @endphp

    @foreach($cards as $card)
        @php
            $studentSchool = $card->student->school ?? $school;
            $studentSubjects = $card->student->activeSubjects->pluck('id');
            $studentPapers = $papers->whereIn('subject_id', $studentSubjects);
        @endphp
        <div class="admit-container {{ $bgClass }}">
            @if($bg)
                <img src="{{ asset($bg) }}" class="bg-print-img" alt="Background">
            @endif

            <div class="header">
                <h1>{{ $studentSchool->name }}</h1>
                <p><span class="font-bold">Phone:</span> {{ $studentSchool->phone ?? '-' }} &nbsp;|&nbsp; <span class="font-bold">Email:</span> {{ $studentSchool->email ?? '-' }}</p>
                <p><span class="font-bold">Address:</span> {{ $studentSchool->address ?? '-' }}</p>
            </div>

            <div class="title">
                <span>Student Admit Card</span>
            </div>

            <div class="student-info-wrapper">
                <div class="student-info">
                    <table>
                        <tr>
                            <td class="label">Student Name:</td>
                            <td colspan="5">{{ $card->student->full_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Session:</td>
                            <td colspan="5">{{ $session->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Class:</td>
                            <td>{{ $card->student->schoolClass->name }}</td>
                            <td class="label" style="width: 70px;">Section:</td>
                            <td>{{ $card->student->section?->name ?? 'A' }}</td>
                            <td class="label" style="width: 130px; text-align: right; padding-right: 10px;">Exam Roll Number:</td>
                            <td>{{ $card->exam_roll_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">Phone:</td>
                            <td colspan="2">{{ $card->student->phone ?? '-' }}</td>
                            <td class="label" style="text-align: right; padding-right: 10px;" colspan="2">Email:</td>
                            <td>{{ $card->student->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                @if($showPhoto && $card->student->photo)
                    <div>
                        <img src="{{ asset($card->student->photo) }}" class="photo" alt="Photo">
                    </div>
                @elseif($showPhoto)
                    <div style="width: 90px; height: 110px; border: 1px solid #111; margin-left: 20px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666; text-align: center;">
                        No Photo
                    </div>
                @endif
            </div>

            <div class="exam-info">
                Exam: {{ $exam->title }} ({{ $exam->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y')) }} - {{ $exam->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y')) }}) &nbsp; Class: {{ $card->student->schoolClass->name }}
            </div>

            <table class="papers-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Paper Code</th>
                        <th>Date</th>
                        <th>Timing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentPapers as $paper)
                        <tr>
                            <td>{{ $paper->subject_name }}</td>
                            <td>{{ $paper->subject_type ?? 'Theory' }}</td>
                            <td>{{ $paper->paper_code ?? '—' }}</td>
                            <td>{{ $paper->paper_date?->format(\App\Models\SystemSetting::getSetting('date_format', 'd-m-Y')) }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($paper->start_time)->format('h:i a') }} - {{ \Carbon\Carbon::parse($paper->end_time)->format('h:i a') }}
                            </td>
                        </tr>
                    @endforeach
                    @if($exam->exam_center)
                    <tr>
                        <td class="font-bold">Exam Center</td>
                        <td colspan="4">{{ $exam->exam_center }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="footer">
                <span class="signature-line"></span><br>
                <span class="auth-by">Authorized By</span>
            </div>
        </div>
    @endforeach
</body>
</html>