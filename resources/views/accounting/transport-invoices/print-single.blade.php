<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transport Invoice - {{ $transportInvoice->invoice_title }}</title>
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { margin: 0; }
        body { font-family: sans-serif; font-size: 13px; margin: 0; padding: 0; color: #111; background: #f3f4f6; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header { text-align: center; margin-bottom: 20px; line-height: 1.4; }
        .header h1 { font-size: 20px; font-weight: bold; margin: 0 0 5px 0; }
        .header p { margin: 2px 0; font-size: 11px; }
        
        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
        }
        .invoice-container.has-bg { }
        .invoice-container.has-bg > img.bg-print-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; opacity: 0.85; }
        .invoice-container > * { position: relative; z-index: 1; }

        .content-box {
            border: none;
            margin-top: 20px;
            background: transparent;
        }

        .invoice-title-bar {
            font-size: 16px;
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid #d2d5db;
            background: transparent;
        }
        
        .details-container {
            display: table;
            width: 100%;
            padding: 20px;
        }
        .details-left, .details-right {
            display: table-cell;
            vertical-align: top;
        }
        .details-right {
            text-align: right;
            width: 35%;
        }
        .details-left table {
            width: 100%;
        }
        .details-left table td {
            padding: 4px 0;
            font-size: 13px;
        }
        .details-left table td.label {
            font-weight: bold;
            width: 150px;
        }

        .details-right-lines div {
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
        }
        .details-right-lines .title-right {
            font-weight: normal;
            margin-bottom: 8px;
        }
        
        .tables-wrapper {
            padding: 0 20px 20px 20px;
        }

        table.fee-table, table.summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.fee-table th, table.fee-table td, table.summary-table th, table.summary-table td {
            border: 1px solid #d2d5db;
            padding: 12px;
            text-align: left;
        }
        table.fee-table th, table.summary-table th {
            font-weight: bold;
        }
        
        .total-paid {
            font-weight: bold;
            font-size: 15px;
            padding: 10px 20px 20px 20px;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white; font-size: 16px; }
            .header h1 { font-size: 28px; }
            .header p { font-size: 14px; }
            .invoice-title-bar { font-size: 20px; padding: 20px; }
            .details-left table td { font-size: 15px; padding: 8px 0; }
            .details-right-lines div { font-size: 15px; margin-bottom: 10px; }
            table.fee-table th, table.fee-table td, table.summary-table th, table.summary-table td { padding: 18px; font-size: 15px; }
            .total-paid { font-size: 18px; }
            .invoice-container { max-width: 100%; margin: 0; padding: 30px; box-shadow: none; border: 0; page-break-after: always; break-after: page; min-height: 95vh; }
            .invoice-container:last-of-type { page-break-after: auto; break-after: auto; }
            .content-box { border: none; margin-top: 30px; }
        }
        .toolbar {
            text-align: right; margin-bottom: 20px; max-width: 800px; margin: 20px auto 0 auto; display: flex; justify-content: flex-end; gap: 10px;
        }
        .toolbar button {
            padding: 8px 16px; background: #000; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-family: sans-serif;
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.history.back()" style="background: #e5e7eb; color: #374151;">Cancel</button>
        <button type="button" onclick="window.print()">Print Invoice</button>
    </div>

        @php
        $copies = \App\Models\SchoolSetting::getSetting($school->id, $transportInvoice->academic_session_id ?? session('selected_academic_session_id') ?? \App\Models\AcademicSession::latest('id')->value('id'), 'invoice_copy', 'No') === 'Yes' ? 2 : 1;
        $bg = \App\Models\SchoolSetting::getSetting($school->id, $transportInvoice->academic_session_id ?? session('selected_academic_session_id') ?? \App\Models\AcademicSession::latest('id')->value('id'), 'invoice_background');
        $bgClass = $bg ? 'has-bg' : '';
    @endphp

    @for($i = 0; $i < $copies; $i++)
        @php
            $copyLabel = '';
            if ($copies == 2) {
                $copyLabel = $i == 0 ? ' (Student Copy)' : ' (School Copy)';
            }
        @endphp
    <div class="invoice-container {{ $bgClass }}">
        @if($bg)
            <img src="{{ asset($bg) }}" class="bg-print-img" alt="Background">
        @endif
        <div class="header">
            <h1>{{ $school->name }}</h1>
            <p><span class="font-bold">Phone:</span> {{ $school->phone ?? '555-4399' }} &nbsp;|&nbsp; <span class="font-bold">Email:</span> {{ $school->email ?? 'school@example.com' }}</p>
            <p><span class="font-bold">Address:</span> {{ $school->address ?? '123 School Road, City' }}</p>
        </div>

        <div class="content-box">
            <div class="invoice-title-bar">
                <span class="font-bold">Transport Invoice:</span> {{ $transportInvoice->invoice_title }}{{ $copyLabel ?? '' }} <span class="font-bold">Invoice No.</span> {{ str_pad($transportInvoice->id, 5, '0', STR_PAD_LEFT) }}
            </div>

            <div class="details-container">
                <div class="details-left">
                    <table>
                        <tr><td class="label">Student Name:</td><td>{{ $transportInvoice->student->first_name }} {{ $transportInvoice->student->last_name }}</td></tr>
                        <tr><td class="label">Enrollment Number:</td><td>{{ $transportInvoice->student->admission_no }}</td></tr>
                        <tr><td class="label">Phone:</td><td>{{ $transportInvoice->student->phone ?? '-' }}</td></tr>
                        <tr><td class="label">Email:</td><td>{{ $transportInvoice->student->email ?? '-' }}</td></tr>
                        <tr><td class="label">Class:</td><td>{{ $transportInvoice->student->schoolClass->name ?? '-' }}</td></tr>
                        <tr><td class="label">Section:</td><td>{{ $transportInvoice->student->section->name ?? '-' }}</td></tr>
                        <tr><td class="label">Roll Number:</td><td>{{ $transportInvoice->student->roll_number ?? '-' }}</td></tr>
                        <tr><td class="label">Father's Name:</td><td>{{ $transportInvoice->student->father_name ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="details-right">
                    <div class="details-right-lines">
                        <div class="title-right">{{ $transportInvoice->invoice_title }}</div>
                        <div>Date: {{ $transportInvoice->issued_at->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) }}</div>
                        <div>Start Date: {{ $transportInvoice->start_date ? $transportInvoice->start_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '-' }}</div>
                        <div>End Date: {{ $transportInvoice->end_date ? $transportInvoice->end_date->format(\App\Models\SystemSetting::getSetting('date_format', 'Y-m-d')) : '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="tables-wrapper">
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th style="width: 50%">Transport Route</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $transportInvoice->transportRoute->route_name ?? $transportInvoice->invoice_title }}</td>
                            <td>{{ (float)$transportInvoice->total_amount }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Total</td>
                            <td class="font-bold">{{ (float)$transportInvoice->total_amount }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Total</th>
                            <th>Amount</th>
                            <th>Discount (%)</th>
                            <th>Due</th>
                            <th>After Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $transportInvoice->total_amount, 2) }}</td>
                            <td>{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format((float) $transportInvoice->total_amount, 2) }}</td>
                            <td>(0%)</td>
                            <td>{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format(max(0, (float) $transportInvoice->total_amount - (float) $transportInvoice->amount_paid), 2) }}</td>
                            <td>{{\App\Models\SchoolSetting::getCurrencySymbol()}}{{ number_format(max(0, (float) $transportInvoice->total_amount - (float) $transportInvoice->amount_paid), 2) }}</td>
                            <td class="font-bold">{{ ucfirst($transportInvoice->status) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="total-paid">
                <div>TOTAL PAID:</div>
                <div>{{ (float) $transportInvoice->amount_paid > 0 ? '$' . number_format((float) $transportInvoice->amount_paid, 2) : '$0.00' }}</div>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endfor
</body>
</html>
