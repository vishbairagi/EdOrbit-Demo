<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Report - {{ $school->name ?? 'EdOrbit' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background: #fff;
            color: #1f2937;
            padding: 24px;
            font-size: 12px;
        }
        .header {
            border-bottom: 2px solid #B47A42;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #211B17;
        }
        .report-title {
            font-size: 14px;
            font-weight: 600;
            color: #B47A42;
            margin-top: 2px;
        }
        .meta-text {
            font-size: 11px;
            color: #6b7280;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }
        th {
            background-color: #B47A42;
            color: #ffffff;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background-color: #fcfaf8;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
        }
        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-today {
            background: #fef3c7;
            color: #92400e;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .actions-no-print {
            margin-bottom: 16px;
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
        .btn-primary {
            background: #B47A42;
            color: #fff;
            border-color: #B47A42;
        }
        @media print {
            .actions-no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                margin: 1cm;
                size: landscape;
            }
        }
    </style>
</head>
<body>

    <div class="actions-no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Homework List
        </button>
        <button onclick="window.close()" class="btn">
            ✕ Close Window
        </button>
    </div>

    <div class="header">
        <div>
            <div class="school-name">{{ $school->name ?? 'EdOrbit' }}</div>
            <div class="report-title">Academic Homework Report</div>
        </div>
        <div class="meta-text">
            <div><strong>Session:</strong> {{ $session->name ?? 'Current' }}</div>
            <div><strong>Printed On:</strong> {{ date('M d, Y h:i A') }}</div>
            <div><strong>Total Homeworks:</strong> {{ $homeworks->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Title</th>
                <th>Class &amp; Section</th>
                <th>Subject</th>
                <th>Assigned Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Added By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($homeworks as $idx => $hw)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $hw->title }}</strong>
                        @if($hw->description)
                            <div style="color: #6b7280; font-size: 10px; margin-top: 2px;">
                                {{ \Illuminate\Support\Str::limit($hw->description, 100) }}
                            </div>
                        @endif
                    </td>
                    <td>{{ $hw->schoolClass?->name ?? 'N/A' }}{{ $hw->section ? ' · '.$hw->section->name : ' · All' }}</td>
                    <td>{{ $hw->subject?->name ?? 'N/A' }}</td>
                    <td>{{ $hw->homework_date ? $hw->homework_date->format('M d, Y') : '-' }}</td>
                    <td>{{ $hw->due_date ? $hw->due_date->format('M d, Y') : '-' }}</td>
                    <td>
                        @if($hw->is_overdue)
                            <span class="status-badge status-overdue">Past Due</span>
                        @elseif($hw->days_remaining === 0)
                            <span class="status-badge status-today">Due Today</span>
                        @else
                            <span class="status-badge status-active">Active ({{ $hw->days_remaining }}d left)</span>
                        @endif
                    </td>
                    <td>{{ $hw->added_by ?: 'Teacher' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 24px; color: #9ca3af;">
                        No homework assignments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
