<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Timetable - {{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: #ffffff;
            color: #111827;
            padding: 20px;
            font-size: 10.5px;
        }

        .school-header {
            text-align: center;
            padding-bottom: 8px;
            margin-bottom: 10px;
            border-bottom: 1px solid #9ca3af;
        }

        .school-name {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .school-meta {
            font-size: 10px;
            color: #4b5563;
            margin-top: 3px;
        }

        .school-address {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .timetable-title {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: auto;
        }

        td {
            border: 1px solid #9ca3af;
            padding: 6px 8px;
            vertical-align: top;
            line-height: 1.35;
        }

        .day-col {
            width: 85px;
            min-width: 85px;
            max-width: 95px;
            font-weight: 700;
            color: #111827;
            background: #ffffff;
        }

        .slot-cell {
            vertical-align: top;
        }

        .slot-subject {
            font-weight: 700;
            font-size: 10.5px;
            color: #111827;
        }

        .slot-time {
            font-size: 10px;
            color: #374151;
            margin-top: 1px;
        }

        .slot-room {
            font-size: 10px;
            color: #6b7280;
            margin-top: 1px;
        }

        .slot-teacher {
            font-size: 10px;
            color: #4b5563;
            font-style: italic;
            margin-top: 1px;
        }

        .actions-bar {
            margin-bottom: 14px;
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
        }

        .btn-green {
            background-color: #10b981 !important;
            color: #ffffff !important;
            border-color: #10b981 !important;
        }

        @media print {
            .actions-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                margin: 8mm;
                size: landscape;
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <button onclick="window.print()" class="btn btn-green">
            🖨️ Print Class Timetable
        </button>
        <button onclick="window.close()" class="btn">
            ✕ Close Window
        </button>
    </div>

    {{-- School Header (Matching Attached PDF) --}}
    <div class="school-header">
        <div class="school-name">{{ $school->name ?? 'EdOrbit Academy' }}</div>
        <div class="school-meta">
            @if($school->phone)
                Phone: {{ $school->phone }} &nbsp;|&nbsp;
            @endif
            @if($school->email)
                Email: {{ $school->email }}
            @endif
        </div>
        @if($school->address)
            <div class="school-address">Address: {{ $school->address }}</div>
        @endif
    </div>

    {{-- Class Sub-title (Matching Attached PDF) --}}
    <div class="timetable-title">
        Class Timetable: {{ $schoolClass->name }}{{ $section ? ' - ' . $section->name : '' }}
    </div>

    @php
        $maxSlots = 0;
        foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $d) {
            $cnt = isset($timetableGrid[$d]) ? count($timetableGrid[$d]) : 0;
            if ($cnt > $maxSlots) $maxSlots = $cnt;
        }
        $maxSlots = max(1, $maxSlots);
    @endphp

    {{-- 7-Day Timetable Table Grid (Horizontal columns matching PDF) --}}
    <table>
        <tbody>
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $dayName)
                @php
                    $daySlots = $timetableGrid[$dayName] ?? collect();
                @endphp
                <tr>
                    <td class="day-col">{{ $dayName }}</td>
                    @if($daySlots->isNotEmpty())
                        @foreach($daySlots as $slot)
                            <td class="slot-cell">
                                <div class="slot-subject">
                                    {{ $slot->subject?->name ?? 'Subject' }}
                                    @if($slot->subject?->code)
                                        ({{ $slot->subject->code }})
                                    @endif
                                </div>
                                <div class="slot-time">{{ $slot->formatted_time_range }}</div>
                                <div class="slot-room">
                                    {{ $slot->room_number ? (str_starts_with(strtolower($slot->room_number), 'room') ? $slot->room_number : 'Room No. ' . $slot->room_number) : 'Room No. -' }}
                                </div>
                                <div class="slot-teacher">
                                    - {{ $slot->teacher?->name ?? 'Demo Teacher' }}
                                </div>
                            </td>
                        @endforeach
                        @for($i = $daySlots->count(); $i < $maxSlots; $i++)
                            <td class="slot-cell"></td>
                        @endfor
                    @else
                        @for($i = 0; $i < $maxSlots; $i++)
                            <td class="slot-cell"></td>
                        @endfor
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
